<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderRevision;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class OrderRevisionController extends Controller
{
    public function revise(Request $request, $id): JsonResponse
    {
        $user = auth()->user();

        if (!$user->can('orders_revise')) {
            return response()->json([
                'message' => 'Anda tidak memiliki izin untuk merevisi order',
            ], 403);
        }

        $validated = $request->validate([
            'items'                     => 'required|array|min:1',
            // item lama: wajib ada id
            'items.*.id'                => 'nullable|integer|exists:order_items,id',
            'items.*.variant_id' => 'nullable|integer|exists:product_variants,id',
            // item dari product lookup — product_id wajib untuk item BARU
            'items.*.product_id'        => 'nullable|integer|exists:products,id',
            'items.*.product_name'      => 'required|string|max:255',
            'items.*.variant_label'     => 'nullable|string|max:100',
            'items.*.variant_names'     => 'nullable|string|max:255',
            'items.*.qty'               => 'required|integer|min:1',
            // sell_price dari frontend HANYA referensi tampilan;
            // harga aktual diambil dari DB di bawah
            'items.*.sell_price'        => 'required|integer|min:0',
            'items.*.subtotal'          => 'required|integer|min:0',

            'shipping_courier'          => 'sometimes|nullable|string|max:100',
            'shipping_service'          => 'sometimes|nullable|string|max:100',
            'shipping_name'             => 'sometimes|nullable|string|max:255',
            'shipping_cost'             => 'sometimes|nullable|integer|min:0',
            'shipping_etd'              => 'nullable|string|max:100',

            'note'                      => 'nullable|string|max:1000',
        ]);

        // Lock order untuk cegah race condition
        $order = Order::with('items')->lockForUpdate()->findOrFail($id);

        if (!$order->isRevisable()) {
            return response()->json([
                'message' => "Order sudah berstatus '{$order->status}', tidak bisa direvisi",
            ], 422);
        }

        // ── Resolve harga dari database (anti trusted-frontend) ──────────────
        // Untuk item yang punya product_id, ambil sell_price dari DB.
        // Untuk item lama tanpa product_id, pertahankan harga existing dari order.
        $productIds = collect($validated['items'])
            ->pluck('product_id')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $products = $productIds
            ? Product::whereIn('id', $productIds)
             ->with(['activeVariants'])  // ← tambahkan ini
             ->get()
             ->keyBy('id')
        : collect();

        $existingItems = $order->items->keyBy('id');

        // Bangun resolved items: harga dari DB, bukan dari request
        $resolvedItems = [];
        foreach ($validated['items'] as $idx => $item) {
            $resolvedItem = $item;

            if (!empty($item['product_id']) && $products->has($item['product_id'])) {
            $product = $products->get($item['product_id']);

            // Cek variant dulu, fallback ke product sell_price
            $variantId = $item['variant_id'] ?? null;
            $variant   = $variantId
                ? $product->activeVariants->firstWhere('id', $variantId)
                : $product->activeVariants->first();

            $resolvedItem['sell_price']   = (int) ($variant?->sell_price ?? $product->sell_price);
            $resolvedItem['product_name'] = $resolvedItem['product_name'] ?: $product->name;
        } elseif (!empty($item['id']) && $existingItems->has($item['id'])) {
                // Item lama tanpa product_id: pertahankan harga existing
                // KECUALI user punya permission ubah harga
                $existing = $existingItems->get($item['id']);
                if (!$user->can('orders_revise_price')) {
                    $resolvedItem['sell_price'] = (int) $existing->sell_price;
                }
                // Jika punya permission, pakai sell_price dari request (sudah tervalidasi)
            }
            // Recalculate subtotal dari resolved price
            $resolvedItem['subtotal'] = (int) $resolvedItem['qty'] * (int) $resolvedItem['sell_price'];

            $resolvedItems[] = $resolvedItem;
        }

        // ── Guard: perubahan harga (setelah resolved dari DB) ────────────────
        if ($this->hasPriceChange($order, $resolvedItems)) {
            if (!$user->can('orders_revise_price')) {
                return response()->json([
                    'message' => 'Anda tidak memiliki izin untuk mengubah harga item. Hubungi manager',
                ], 403);
            }
        }

        // ── Guard: perubahan kurir ────────────────────────────────────────────
        $courierChanged = $this->hasCourierChange($order, $validated);
        if ($courierChanged && !$user->can('orders_revise_courier')) {
            return response()->json([
                'message' => 'Anda tidak memiliki izin untuk mengubah jasa pengiriman',
            ], 403);
        }

        // ── Stock validation ──────────────────────────────────────────────────
        $stockErrors = $this->validateStock($resolvedItems, $existingItems, $products);
        if (!empty($stockErrors)) {
            return response()->json([
                'message' => 'Stok tidak mencukupi',
                'errors'  => $stockErrors,
            ], 422);
        }

        DB::beginTransaction();

        try {
            $before   = $this->snapshotOrder($order);

            // ── Hapus item yang tidak ada di request ──────────────────────────
            $keepIds = collect($resolvedItems)
                ->pluck('id')
                ->filter()
                ->toArray();

            $removedItems = $order->items->whereNotIn('id', $keepIds);
            foreach ($removedItems as $removed) {
                $oldItem = $existingItems->get($removed->id);
                if (!$oldItem) continue;

                $oldVariantId = $oldItem->variant_id ?? null;

                if ($oldVariantId) {
                    DB::table('product_variants')
                        ->where('id', $oldVariantId)
                        ->increment('stock_qty', $oldItem->qty);
                } elseif ($oldItem->product_id) {
                    DB::table('products')
                        ->where('id', $oldItem->product_id)
                        ->increment('stock_qty', $oldItem->qty);
                }
            }


            $order->items()->whereNotIn('id', $keepIds)->delete();

            // ── Update / Insert items ─────────────────────────────────────────
            foreach ($resolvedItems as $itemData) {
                $payload = [
                    'product_id'    => $itemData['product_id'] ?? null,
                    'variant_id'    => $itemData['variant_id'] ?? null,
                    'product_name'  => $itemData['product_name'],
                    'variant_label' => $itemData['variant_label'] ?? null,
                    'variant_names' => $itemData['variant_names'] ?? null,
                    'qty'           => $itemData['qty'],
                    'sell_price'    => $itemData['sell_price'],
                    'subtotal'      => $itemData['subtotal'],
                ];

                if (!empty($itemData['id'])) {
                    OrderItem::where('id', $itemData['id'])
                        ->where('order_id', $order->id)  // security: pastikan item milik order ini
                        ->update($payload);
                } else {
                    OrderItem::create(array_merge($payload, [
                        'order_id'   => $order->id,
                        'variant_id' => $itemData['variant_id'] ?? null, // tambah ini
                    ]));
                }
            }

            foreach ($resolvedItems as $itemData) {
                $variantId = $itemData['variant_id'] ?? null;
                $productId = $itemData['product_id'] ?? null;
                $newQty    = (int) $itemData['qty'];

                $oldQty = 0;
                if (!empty($itemData['id']) && $existingItems->has($itemData['id'])) {
                    $oldQty = (int) $existingItems->get($itemData['id'])->qty;
                }

                $qtyDiff = $newQty - $oldQty;
                if ($qtyDiff === 0) continue;

                if ($variantId) {
                    DB::table('product_variants')
                        ->where('id', $variantId)
                        ->decrement('stock_qty', $qtyDiff);
                } elseif ($productId) {
                    DB::table('products')
                        ->where('id', $productId)
                        ->decrement('stock_qty', $qtyDiff);
                }
            }

            // ── Recalculate totals ────────────────────────────────────────────
            $newSubtotal     = (int) collect($resolvedItems)->sum('subtotal');
            $newShippingCost = $courierChanged
                ? (int) ($validated['shipping_cost'] ?? $order->shipping_cost)
                : (int) $order->shipping_cost;
            $discountAmount  = (int) $order->discount_amount;
            $newTotal        = max(0, $newSubtotal + $newShippingCost - $discountAmount);

            $updateData = [
                'subtotal'        => $newSubtotal,
                'shipping_cost'   => $newShippingCost,
                'total_price'     => $newTotal,
                'revised_at'      => now(),
                'revised_by'      => $user->id,
                'revision_count'  => $order->revision_count + 1,
            ];

            if ($courierChanged) {
                $updateData['shipping_courier'] = $validated['shipping_courier'];
                $updateData['shipping_service'] = $validated['shipping_service'];
                $updateData['shipping_name']    = $validated['shipping_name'];
                $updateData['shipping_etd']     = $validated['shipping_etd'] ?? $order->shipping_etd;
            }

            $order->update($updateData);
            $order->refresh()->load('items');

            // ── Snapshot after + audit trail ──────────────────────────────────
            $after          = $this->snapshotOrder($order);
            $changesSummary = $this->buildChangesSummary($before, $after, $resolvedItems);

            OrderRevision::create([
                'order_id'        => $order->id,   // fix typo
                'revised_by'      => $user->id,
                'before'          => $before,
                'after'           => $after,
                'changes_summary' => $changesSummary,
                'note'            => $validated['note'] ?? null,
                'created_at'      => now(),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Order berhasil direvisi',
                'data'    => $order,
                'summary' => $changesSummary,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal revisi order',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/orders/{id}/revisions
     */
    public function history($id): JsonResponse
    {
        $user = auth()->user();

        if (!$user->can('orders_revise')) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $order = Order::findOrFail($id);

        $revisions = OrderRevision::with('revisor:id,name,email')
            ->where('order_id', $order->id)
            ->latest('created_at')
            ->get()
            ->map(fn($r) => [
                'id'              => $r->id,
                'revised_by_name' => $r->revisor?->name ?? 'Unknown',
                'changes_summary' => $r->changes_summary,
                'note'            => $r->note,
                'created_at'      => $r->created_at,
            ]);

        return response()->json([
            'data'           => $revisions,
            'revision_count' => $order->revision_count,
        ]);
    }

    /**
     * GET /api/products/search?q=xxx
     * Endpoint untuk product lookup di modal revisi.
     */
    public function searchProducts(Request $request): JsonResponse
    {
        $user = auth()->user();

        if (!$user->can('orders_revise')) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $q = trim($request->get('q', ''));

        $products = Product::query()
            ->where('published', true)
            ->where(function ($query) use ($q) {
                $query->where('products.name', 'like', "%{$q}%")
                    ->orWhere('products.alternative_name', 'like', "%{$q}%")
                    ->orWhere('products.sku', 'like', "%{$q}%");
            })
            ->select('id', 'name', 'sku', 'track_inventory', 'photo_1')
            ->with(['variants' => function ($query) {
                $query->where('is_active', 1)
                    ->orderBy('position')
                    ->select('id', 'product_id', 'label', 'sku', 'sell_price', 'stock_qty', 'hold_qty');
            }])
            ->limit(20)
            ->get()
            ->flatMap(function ($product) {
                $variants = $product->variants;

                // Produk tidak punya variant aktif — skip
                if ($variants->isEmpty()) {
                    return [];
                }

                // Kalau hanya 1 variant, tampilkan sebagai 1 item (tanpa label variant)
                if ($variants->count() === 1) {
                    $v = $variants->first();
                    return [[
                        'id'              => $product->id,
                        'variant_id'      => $v->id,
                        'name'            => $product->name,
                        'sku'             => $v->sku ?: $product->sku,
                        'variant_label'   => null,
                        'variant_names'   => null,
                        'sell_price'      => (int) $v->sell_price,
                        'available_stock' => $product->track_inventory
                            ? max(0, (int) $v->stock_qty - (int) $v->hold_qty)
                            : null,
                        'track_inventory' => (bool) $product->track_inventory,
                        'photo'           => $product->photo_1,
                    ]];
                }

                // Multi-variant: tampilkan tiap variant sebagai row terpisah
                return $variants->map(fn($v) => [
                    'id'              => $product->id,
                    'variant_id'      => $v->id,
                    'name'            => $product->name,
                    'sku'             => $v->sku ?: $product->sku,
                    'variant_label'   => $v->label,
                    'variant_names'   => $v->label,
                    'sell_price'      => (int) $v->sell_price,
                    'available_stock' => $product->track_inventory
                        ? max(0, (int) $v->stock_qty - (int) $v->hold_qty)
                        : null,
                    'track_inventory' => (bool) $product->track_inventory,
                    'photo'           => $product->photo_1,
                ])->toArray();
            });

        return response()->json(['data' => $products]);
    }

    // ─── Private Helpers ──────────────────────────────────────────────────────

    private function snapshotOrder(Order $order): array
    {
        return [
            'subtotal'         => (int) $order->subtotal,
            'shipping_courier' => $order->shipping_courier,
            'shipping_service' => $order->shipping_service,
            'shipping_cost'    => (int) $order->shipping_cost,
            'total_price'      => (int) $order->total_price,
            'items'            => $order->items->map(fn($i) => [
                'id'            => $i->id,
                'product_name'  => $i->product_name,
                'variant_names' => $i->variant_names,  // fix: was variant_name
                'qty'           => $i->qty,
                'sell_price'    => (int) $i->sell_price,
                'subtotal'      => (int) $i->subtotal,
            ])->toArray(),
        ];
    }

    private function hasPriceChange(Order $order, array $newItems): bool
    {
        $existingPrices = $order->items->keyBy('id');

        foreach ($newItems as $item) {
            if (empty($item['id'])) {
                continue; // item baru tidak dihitung sebagai "perubahan harga"
            }
            $existing = $existingPrices->get($item['id']);
            if ($existing && (int) $existing->sell_price !== (int) $item['sell_price']) {
                return true;
            }
        }

        return false;
    }

    private function hasCourierChange(Order $order, array $validated): bool
    {
        if (!isset($validated['shipping_courier'])) {
            return false;
        }

        return $order->shipping_courier !== $validated['shipping_courier']
            || $order->shipping_service !== $validated['shipping_service']
            || (int) $order->shipping_cost !== (int) ($validated['shipping_cost'] ?? $order->shipping_cost);
    }

    /**
     * Validasi stok: pastikan stok tersedia untuk qty yang diminta.
     * Hanya untuk produk yang track_inventory = true.
     * Untuk item yang sudah ada di order, hitung selisih qty (bukan dari nol).
     */
    private function validateStock(
        array $resolvedItems,
        \Illuminate\Support\Collection $existingItems,
        \Illuminate\Support\Collection $products
    ): array {
        $errors = [];

        foreach ($resolvedItems as $item) {
            $productId = $item['product_id'] ?? null;
            if (!$productId || !$products->has($productId)) {
                continue;
            }

            $product = $products->get($productId);
            if (!$product->track_inventory) {
                continue;
            }

            // ← Ambil stok dari variant, bukan dari product
            $variantId = $item['variant_id'] ?? null;

            if ($variantId) {
                // Cari variant spesifik yang dipilih
                $variant = $product->activeVariants->firstWhere('id', $variantId);
            } else {
                // Fallback: ambil variant pertama (produk single variant)
                $variant = $product->activeVariants->first();
            }

            if (!$variant) {
                continue;
            }

            $availableStock = max(0, (int) $variant->stock_qty - (int) $variant->hold_qty);

            // Hitung qty lama agar tidak double-count hold
            $oldQty = 0;
            if (!empty($item['id']) && $existingItems->has($item['id'])) {
                $existing = $existingItems->get($item['id']);
                if ($existing->product_id == $productId) {
                    $oldQty = (int) $existing->qty;
                }
            }

            $effectiveAvailable = $availableStock + $oldQty;

            if ((int) $item['qty'] > $effectiveAvailable) {
                $errors[] = [
                    'product_name' => $item['product_name'],
                    'requested'    => $item['qty'],
                    'available'    => $effectiveAvailable,
                    'message'      => "Stok {$item['product_name']} tidak cukup. Tersedia: {$effectiveAvailable}, diminta: {$item['qty']}",
                ];
            }
        }

        return $errors;
    }

    private function buildChangesSummary(array $before, array $after, array $newItems): array
    {
        $changes = [];
        $fmt     = fn($v) => 'Rp' . number_format((int) $v, 0, ',', '.');

        if ($before['subtotal'] !== $after['subtotal']) {
            $changes[] = "Subtotal berubah dari {$fmt($before['subtotal'])} menjadi {$fmt($after['subtotal'])}";
        }

        // fix: was $change[] — typo menyebabkan perubahan kurir tidak ter-record
        if ($before['shipping_courier'] !== $after['shipping_courier']
            || $before['shipping_service'] !== $after['shipping_service']) {
            $changes[] = "Kurir diubah dari {$before['shipping_courier']} {$before['shipping_service']} menjadi {$after['shipping_courier']} {$after['shipping_service']}";
        }

        if ($before['shipping_cost'] !== $after['shipping_cost']) {
            $changes[] = "Ongkir berubah dari {$fmt($before['shipping_cost'])} menjadi {$fmt($after['shipping_cost'])}";
        }

        if ($before['total_price'] !== $after['total_price']) {
            $changes[] = "Total berubah dari {$fmt($before['total_price'])} menjadi {$fmt($after['total_price'])}";
        }

        $beforeItemIds = collect($before['items'])->pluck('id')->toArray();
        $afterItemIds  = collect($newItems)->pluck('id')->filter()->toArray();

        $removedCount = count(array_diff($beforeItemIds, $afterItemIds));
        $addedCount   = collect($newItems)->filter(fn($i) => empty($i['id']))->count();

        if ($removedCount > 0) $changes[] = "{$removedCount} item dihapus dari order";
        if ($addedCount > 0)   $changes[] = "{$addedCount} item baru ditambahkan ke order";

        return $changes ?: ['Tidak ada perubahan signifikan'];
    }
}