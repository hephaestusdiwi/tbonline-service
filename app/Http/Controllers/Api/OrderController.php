<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\LoyaltyPoint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\Product;
use App\Models\PromoCode;
use App\Models\ProductVariant;
use Carbon\Carbon;

class OrderController extends Controller
{
    /**
     * GET /api/orders
     */
    public function index(Request $request)
    {
        $query = Order::with(['items', 'branch'])->latest();

        if ($request->status && in_array($request->status, Order::STATUSES)) {
            $query->where('status', $request->status);
        }

        if ($request->search) {
            $q = $request->search;
            $query->where(function ($q2) use ($q) {
                $q2->where('customer_name', 'like', "%{$q}%")
                   ->orWhere('customer_phone', 'like', "%{$q}%")
                   ->orWhere('invoice_number', 'like', "%{$q}%");
            });
        }

        $orders = $query->paginate($request->per_page ?? 15);

        return response()->json([
            'data' => $orders->items(),
            'meta' => [
                'total'        => $orders->total(),
                'per_page'     => $orders->perPage(),
                'current_page' => $orders->currentPage(),
                'last_page'    => $orders->lastPage(),
            ],
        ]);
    }

    /**
     * POST /api/orders
     */
    public function store(Request $request)
    {
        // ── Tentukan tipe fulfillment ────────────────────────────────────────
        $fulfillmentType  = $request->input('fulfillment_type', 'delivery');
        $isPickup         = $fulfillmentType === 'pickup';
        $isCustomShipping = !$isPickup && $request->input('shipping_courier') === 'custom';

        // ── Validasi bersama (selalu wajib) ──────────────────────────────────
        $rules = [
            'customer_name'    => 'required|string',
            'customer_phone'   => 'required|string',
            'customer_email'   => 'nullable|email',
            'notes'            => 'nullable|string',
            'fulfillment_type' => 'required|in:delivery,pickup',
            'subtotal'         => 'required|integer',
            'total_price'      => 'required|integer',
            'items'            => 'required|array|min:1',
            'items.*.product_name'  => 'required|string',
            'items.*.qty'           => 'required|integer|min:1',
            'items.*.sell_price'    => 'required|integer',
            'items.*.subtotal'      => 'required|integer',
            'items.*.product_id'    => 'nullable|integer',  
            'items.*.variant_id'    => 'nullable|integer',  
        ];

        // ── Validasi kondisional berdasarkan fulfillment_type ────────────────
        if ($isPickup) {
            $rules['branch_id'] = 'required|exists:branches,id';

        } elseif ($isCustomShipping) {
            $rules['address']              = 'required|string';
            $rules['subdistrict']          = 'nullable|string';
            $rules['district']             = 'nullable|string';
            $rules['city']                 = 'nullable|string';
            $rules['province']             = 'nullable|string';
            $rules['postal_code']          = 'nullable|string';
            $rules['shipping_custom_note'] = 'nullable|string|max:500';

        } else {
            $rules['address']          = 'required|string';
            $rules['subdistrict']      = 'required|string';
            $rules['district']         = 'required|string';
            $rules['city']             = 'required|string';
            $rules['province']         = 'required|string';
            $rules['postal_code']      = 'nullable|string';
            $rules['destination_id']   = 'nullable|integer';
            $rules['shipping_courier'] = 'required|string';
            $rules['shipping_service'] = 'required|string';
            $rules['shipping_name']    = 'required|string';
            $rules['shipping_cost']    = 'required|integer';
            $rules['shipping_etd']     = 'nullable|string';
        }

        $validated = $request->validate($rules);

        try {
            DB::beginTransaction();

            // ── Promo code ────────────────────────────────────────────────────
            $discountAmount   = 0;
            $appliedPromoCode = null;

            if ($request->filled('promo_code')) {
                $promo = PromoCode::where('code', strtoupper($request->promo_code))->first();

                if ($promo) {
                    $check = $promo->isValid((int) $request->subtotal, $request->customer_phone);
                    if ($check['valid']) {
                        $discountAmount = $promo->calculateDiscount(
                            (int) $request->subtotal,
                            $isPickup ? 0 : ($isCustomShipping ? 0 : (int) $request->shipping_cost)
                        );
                        $appliedPromoCode = $promo->code;
                        $promo->increment('used_count');
                    }
                }
            }

            // ── Kalkulasi shipping fields ─────────────────────────────────────
            if ($isPickup) {
                $shippingCourier    = 'pickup';
                $shippingService    = 'PICKUP';
                $shippingName       = 'Ambil di Tempat';
                $shippingCost       = 0;
                $shippingEtd        = null;
                $shippingIsCustom   = false;
                $shippingCustomNote = null;
                $address            = null;
                $subdistrict        = null;
                $district           = null;
                $city               = null;
                $province           = null;
                $postalCode         = null;
                $destinationId      = null;

            } elseif ($isCustomShipping) {
                $shippingCourier    = 'custom';
                $shippingService    = 'CUSTOM';
                $shippingName       = 'Atur Sendiri (Diskusi dengan Staff)';
                $shippingCost       = 0;
                $shippingEtd        = null;
                $shippingIsCustom   = true;
                $shippingCustomNote = $request->input('shipping_custom_note');
                $address            = $validated['address'];
                $subdistrict        = $validated['subdistrict'] ?? null;
                $district           = $validated['district'] ?? null;
                $city               = $validated['city'] ?? null;
                $province           = $validated['province'] ?? null;
                $postalCode         = $validated['postal_code'] ?? null;
                $destinationId      = null;

            } else {
                $shippingCourier    = $validated['shipping_courier'];
                $shippingService    = $validated['shipping_service'];
                $shippingName       = $validated['shipping_name'];
                $shippingCost       = (int) $validated['shipping_cost'];
                $shippingEtd        = $validated['shipping_etd'] ?? null;
                $shippingIsCustom   = false;
                $shippingCustomNote = null;
                $address            = $validated['address'];
                $subdistrict        = $validated['subdistrict'];
                $district           = $validated['district'];
                $city               = $validated['city'];
                $province           = $validated['province'];
                $postalCode         = $validated['postal_code'] ?? null;
                $destinationId      = $validated['destination_id'] ?? null;
            }

            // ── Grand total ───────────────────────────────────────────────────
            $grandTotal = (int) $request->subtotal + $shippingCost - $discountAmount;

            // ── Buat order ────────────────────────────────────────────────────
            $order = Order::create([
                'invoice_number'       => Order::generateInvoiceNumber(),
                'customer_name'        => $validated['customer_name'],
                'customer_phone'       => $validated['customer_phone'],
                'customer_email'       => $validated['customer_email'] ?? null,
                'address'              => $address,
                'subdistrict'          => $subdistrict,
                'district'             => $district,
                'city'                 => $city,
                'province'             => $province,
                'postal_code'          => $postalCode,
                'destination_id'       => $destinationId,
                'shipping_courier'     => $shippingCourier,
                'shipping_service'     => $shippingService,
                'shipping_name'        => $shippingName,
                'shipping_cost'        => $shippingCost,
                'shipping_etd'         => $shippingEtd,
                'shipping_is_custom'   => $shippingIsCustom,
                'shipping_custom_note' => $shippingCustomNote,
                'subtotal'             => $request->subtotal,
                'promo_code'           => $appliedPromoCode,
                'discount_amount'      => $discountAmount,
                'total_price'          => $grandTotal,
                'notes'                => $validated['notes'] ?? null,
                'status'               => 'pending',
                'fulfillment_type'     => $fulfillmentType,
                'branch_id'            => $isPickup ? ($validated['branch_id'] ?? null) : null,
            ]);

            foreach ($validated['items'] as $item) {
                \Log::info('item_debug', $item);

                OrderItem::create([
                    'order_id'      => $order->id,
                    'product_id'    => $item['product_id'] ?? null,
                    'variant_id'    => $item['variant_id'] ?? null,
                    'product_name'  => $item['product_name'],
                    'variant_label' => $item['variant_label'] ?? null,
                    'variant_names' => $item['variant_names'] ?? null,
                    'qty'           => $item['qty'],
                    'sell_price'    => $item['sell_price'],
                    'subtotal'      => $item['subtotal'],
                ]);

                // Kurangi stok — boleh minus (oversell di-cover cabang lain)
                if (!empty($item['variant_id'])) {
                    DB::table('product_variants')
                        ->where('id', $item['variant_id'])
                        ->decrement('stock_qty', $item['qty']);

                } elseif (!empty($item['product_id'])) {
                    DB::table('products')
                        ->where('id', $item['product_id'])
                        ->decrement('stock_qty', $item['qty']);
                }
            }

            DB::commit();

            // ── Preview point yang akan didapat (belum di-earn, menunggu konfirmasi) ──
            $pointsWillEarn = LoyaltyPoint::calculateEarnPoints((int) $request->subtotal);

            return response()->json([
                'message' => 'Order berhasil dibuat.',
                'data'    => $order->load(['items', 'branch']),
                'loyalty' => [
                    'points_will_earn' => $pointsWillEarn,
                    'note'             => 'Point diberikan setelah order dikonfirmasi.',
                ],
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal membuat order.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/orders/{id}
     */
    public function show($id)
    {
        $order = Order::with(['items', 'branch', 'confirmer', 'revisor'])
            ->where('id', $id)
            ->orWhere('invoice_number', $id)
            ->firstOrFail();

        // Flatten nama supaya mudah dipakai di frontend
        $data                      = $order->toArray();
        $data['confirmed_by_name'] = $order->confirmer?->name;
        $data['revised_by_name']   = $order->revisor?->name;

        return response()->json(['data' => $data]);
    }

    /**
     * PATCH /api/orders/{id}/status
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status'        => 'required|in:' . implode(',', Order::STATUSES),
            'cancel_reason' => 'nullable|string|required_if:status,cancelled',
        ]);

        $order = Order::with('items')->lockForUpdate()->findOrFail($id);

        if ($order->isFinal()) {
            return response()->json([
                'message' => "Order ini sudah berstatus '{$order->status}', tidak bisa diubah lagi.",
            ], 422);
        }

        if ($order->status === $request->status) {
            return response()->json([
                'message' => "Order sudah berstatus '{$request->status}'.",
            ], 422);
        }

        $order->update([
            'status'        => $request->status,
            'cancel_reason' => $request->status === 'cancelled' ? $request->cancel_reason : null,
            'confirmed_by'  => auth()->id(),
            'confirmed_at'  => now(),
        ]);

        if ($request->status === 'success') {
            LoyaltyPoint::earn(
                phone:         $order->customer_phone,
                subtotal:      (int) $order->subtotal,
                orderId:       $order->id,
                invoiceNumber: $order->invoice_number,
            );
        } elseif ($request->status === 'cancelled') {
            LoyaltyPoint::expireByOrder($order->id);

            foreach ($order->items as $item) {
                if ($item->variant_id) {
                    DB::table('product_variants')->where('id', $item->variant_id)->increment('stock_qty', $item->qty);
                } elseif ($item->product_id) {
                    DB::table('products')->where('id', $item->product_id)->increment('stock_qty', $item->qty);
                }
            }
        }

        $order->refresh();

        return response()->json([
            'message' => 'Status order berhasil diperbarui.',
            'data'    => $order->load('items'),
        ]);
    }

    /**
     * DELETE /api/orders/{id}
     */
    public function destroy($id)
    {
        $order = Order::findOrFail($id);

        if (!auth()->user()?->can('orders_delete')) {
            return response()->json([
                'message' => 'Anda tidak memiliki izin untuk menghapus order',
            ], 403);
        }

        $order->delete();

        Cache::forget('orders_pending_count');

        return response()->json([
            'message' => "Order {$order->invoice_number} berhasil dihapus",
        ]);
    }

    /**
     * POST /api/orders/{id}/request-delete
     */
    public function requestDelete(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|min:10|max:500',
        ]);

        $order = Order::findOrFail($id);
        $user  = auth()->user();

        DB::table('orders_delete_requests')->insertOrIgnore([
            'order_id'     => $order->id,
            'requested_by' => $user->id,
            'reason'       => $request->reason,
            'status'       => 'pending',
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        return response()->json([
            'message' => 'Pengajuan penghapusan order berhasil dikirim ke admin',
        ]);
    }

    /**
     * GET /api/orders/{id}/invoice
     */
    public function invoice($id)
    {
        $order = Order::with(['items', 'branch', 'confirmer', 'revisor'])
            ->where('id', $id)
            ->orWhere('invoice_number', $id)
            ->firstOrFail();

        $store = [
            'name'    => config('app.store_name', 'TB GROUP'),
            'address' => config('app.store_address', ''),
            'phone'   => config('app.store_phone', ''),
            'email'   => config('app.store_email', ''),
            'website' => config('app.url', ''),
        ];

        return response()->json([
            'data'  => $order,
            'store' => $store,
            'printed_by'  => auth()->user()?->name ?? 'Staff',
            'handled_by' => [
            'confirmed_by' => $order->confirmer?->name,
            'confirmed_at' => $order->confirmed_at?->format('d M Y, H:i'),
            'revised_by'   => $order->revisor?->name,
            'revised_at'   => $order->revised_at?->format('d M Y, H:i'),
            ],
        ]);
    }

    /**
     * POST /api/orders/manual
     * Input order manual oleh admin (order offline / titipan customer).
     */
    public function storeManual(Request $request)
    {
        if (!auth()->user()?->can('orders_create')) {
            return response()->json([
                'message' => 'Anda tidak memiliki izin untuk membuat order manual.',
            ], 403);
        }

        $isPickup = $request->input('fulfillment_type') === 'pickup';

        $rules = [
            'customer_name'       => 'required|string',
            'customer_phone'      => 'required|string',
            'customer_email'      => 'nullable|email',
            'notes'               => 'nullable|string',
            'fulfillment_type'    => 'required|in:delivery,pickup',
            'status'              => 'required|in:' . implode(',', \App\Models\Order::STATUSES),
            'cancel_reason'       => 'nullable|string|required_if:status,cancelled',
            'discount_amount'     => 'nullable|integer|min:0',
            'items'               => 'required|array|min:1',
            'items.*.product_id'  => 'required|integer|exists:products,id',
            'items.*.variant_id'  => 'nullable|integer|exists:product_variants,id',
            'items.*.qty'         => 'required|integer|min:1',
        ];

        if ($isPickup) {
            $rules['branch_id'] = 'required|exists:branches,id';
        } else {
            $rules['address']          = 'required|string';
            $rules['subdistrict']      = 'nullable|string';
            $rules['district']         = 'nullable|string';
            $rules['city']             = 'nullable|string';
            $rules['province']         = 'nullable|string';
            $rules['postal_code']      = 'nullable|string';
            $rules['shipping_courier'] = 'required|string';
            $rules['shipping_service'] = 'required|string';
            $rules['shipping_name']    = 'required|string';
            $rules['shipping_cost']    = 'required|integer|min:0';
            $rules['shipping_etd']     = 'nullable|string';
        }

        $validated = $request->validate($rules);

        try {
            DB::beginTransaction();

            // ── Bangun item dari data produk asli — harga & nama TIDAK dipercaya dari client ──
            $orderItems = [];
            $subtotal   = 0;

            foreach ($validated['items'] as $rawItem) {
                $product = Product::findOrFail($rawItem['product_id']);
                $variant = null;

                if (!empty($rawItem['variant_id'])) {
                    $variant = ProductVariant::where('id', $rawItem['variant_id'])
                        ->where('product_id', $product->id)
                        ->firstOrFail();
                } elseif ($product->activeVariants()->exists()) {
                    throw new \Exception("Produk \"{$product->name}\" punya varian, pilih variannya dulu.");
                }

                $qty       = (int) $rawItem['qty'];
                $unitPrice = $variant ? (float) $variant->effective_sell_price : (float) $product->sell_price;
                $lineTotal = $unitPrice * $qty;
                $subtotal += $lineTotal;

                $orderItems[] = [
                    'product_id'    => $product->id,
                    'variant_id'    => $variant?->id,
                    'product_name'  => $product->name,
                    'variant_label' => $variant?->label,
                    'variant_names' => $variant?->label,
                    'qty'           => $qty,
                    'sell_price'    => $unitPrice,
                    'subtotal'      => $lineTotal,
                    '_stock_target' => $variant ? 'variant' : 'product',
                ];
            }

            $discountAmount = (int) ($validated['discount_amount'] ?? 0);
            $shippingCost   = $isPickup ? 0 : (int) $validated['shipping_cost'];
            $grandTotal     = (int) $subtotal + $shippingCost - $discountAmount;

            $status     = $validated['status'];
            $isResolved = $status !== 'pending';

            $order = Order::create([
                'invoice_number'   => Order::generateInvoiceNumber(),
                'customer_name'    => $validated['customer_name'],
                'customer_phone'   => $validated['customer_phone'],
                'customer_email'   => $validated['customer_email'] ?? null,
                'address'          => $isPickup ? null : $validated['address'],
                'subdistrict'      => $isPickup ? null : ($validated['subdistrict'] ?? null),
                'district'         => $isPickup ? null : ($validated['district'] ?? null),
                'city'             => $isPickup ? null : ($validated['city'] ?? null),
                'province'         => $isPickup ? null : ($validated['province'] ?? null),
                'postal_code'      => $isPickup ? null : ($validated['postal_code'] ?? null),
                'shipping_courier' => $isPickup ? 'pickup' : $validated['shipping_courier'],
                'shipping_service' => $isPickup ? 'PICKUP' : $validated['shipping_service'],
                'shipping_name'    => $isPickup ? 'Ambil di Tempat' : $validated['shipping_name'],
                'shipping_cost'    => $shippingCost,
                'shipping_etd'     => $isPickup ? null : ($validated['shipping_etd'] ?? null),
                'subtotal'         => $subtotal,
                'discount_amount'  => $discountAmount,
                'total_price'      => $grandTotal,
                'notes'            => $validated['notes'] ?? null,
                'status'           => $status,
                'cancel_reason'    => $status === 'cancelled' ? $validated['cancel_reason'] : null,
                'fulfillment_type' => $validated['fulfillment_type'],
                'branch_id'        => $isPickup ? $validated['branch_id'] : null,
                'confirmed_by'     => $isResolved ? auth()->id() : null,
                'confirmed_at'     => $isResolved ? now() : null,
            ]);

            foreach ($orderItems as $item) {
                OrderItem::create([
                    'order_id'      => $order->id,
                    'product_id'    => $item['product_id'],
                    'variant_id'    => $item['variant_id'],
                    'product_name'  => $item['product_name'],
                    'variant_label' => $item['variant_label'],
                    'variant_names' => $item['variant_names'],
                    'qty'           => $item['qty'],
                    'sell_price'    => $item['sell_price'],
                    'subtotal'      => $item['subtotal'],
                ]);

                if ($status !== 'cancelled') {
                    if ($item['_stock_target'] === 'variant') {
                        DB::table('product_variants')->where('id', $item['variant_id'])->decrement('stock_qty', $item['qty']);
                    } else {
                        DB::table('products')->where('id', $item['product_id'])->decrement('stock_qty', $item['qty']);
                    }
                }
            }

            if ($status === 'success') {
                LoyaltyPoint::earn(
                    phone:         $order->customer_phone,
                    subtotal:      (int) $order->subtotal,
                    orderId:       $order->id,
                    invoiceNumber: $order->invoice_number,
                );
            }

            DB::commit();
            Cache::forget('orders_pending_count');

            return response()->json([
                'message' => 'Order manual berhasil dibuat.',
                'data'    => $order->load(['items', 'branch']),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal membuat order manual.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/orders/stats
     */
    public function stats()
    {
        $total        = Order::count();
        $success      = Order::where('status', 'success')->count();
        $cancelled    = Order::where('status', 'cancelled')->count();
        $pending      = Order::where('status', 'pending')->count();
        $diproses     = Order::where('status', 'diproses')->count();
        $totalRevenue = Order::where('status', 'success')->sum('total_price');

        $activeProducts = Product::where('published', 1)->count();
        $lowStockCount  = Product::where('published', 1)
            ->whereHas('variants', fn($q) => $q->whereColumn('stock_qty', '<=', 'low_stock_alert'))
            ->count();

        $lowStock = Product::where('published', 1)
            ->whereHas('variants', fn($q) => $q->whereColumn('stock_qty', '<=', 'low_stock_alert'))
            ->with(['variants' => fn($q) => $q->whereColumn('stock_qty', '<=', 'low_stock_alert')
                                              ->orderBy('stock_qty')])
            ->get()
            ->map(fn($p) => [
                'id'    => $p->id,
                'name'  => $p->name,
                'stock' => $p->variants->min('stock_qty'),
            ]);

        $pendingOrders = Order::where('status', 'pending')
            ->orderBy('created_at')
            ->limit(10)
            ->get(['id', 'invoice_number', 'customer_name', 'created_at'])
            ->map(fn($o) => [
                'id'             => $o->id,
                'invoice_number' => $o->invoice_number,
                'customer_name'  => $o->customer_name,
                'age'            => Carbon::parse($o->created_at)->diffForHumans(),
            ]);

        $expiringPromos = PromoCode::where('is_active', true)
            ->whereNotNull('expired_at')
            ->whereBetween('expired_at', [now(), now()->addDays(7)])
            ->orderBy('expired_at')
            ->get(['id', 'code', 'expired_at'])
            ->map(fn($p) => [
                'id'        => $p->id,
                'name'      => $p->code,
                'days_left' => Carbon::parse($p->expired_at)->diffInDays(now()),
            ]);

        $topCouriers = Order::select('shipping_courier', DB::raw('COUNT(*) as total'))
            ->groupBy('shipping_courier')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(fn($item) => [
                'courier' => strtoupper($item->shipping_courier),
                'total'   => $item->total,
            ]);

        $topProducts = OrderItem::select(
                'order_items.product_name',
                DB::raw('SUM(order_items.qty) as total_sold'),
                DB::raw('SUM(order_items.subtotal) as total_revenue')
            )
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', 'success')
            ->groupBy('order_items.product_name')
            ->orderByDesc('total_sold')
            ->limit(10)
            ->get()
            ->map(fn($item) => [
                'product'       => $item->product_name,
                'total_sold'    => (int) $item->total_sold,
                'total_revenue' => (int) $item->total_revenue,
            ]);

        $monthlyTrend = Order::select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                DB::raw("SUM(CASE WHEN status = 'success'   THEN 1 ELSE 0 END) as success"),
                DB::raw("SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled"),
                DB::raw("SUM(CASE WHEN status = 'pending'   THEN 1 ELSE 0 END) as pending"),
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN status = 'success' THEN total_price ELSE 0 END) as revenue")
            )
            ->where('created_at', '>=', now()->subMonths(6)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return response()->json([
            'summary' => [
                'total_orders'    => $total,
                'total_success'   => $success,
                'total_cancelled' => $cancelled,
                'total_pending'   => $pending,
                'total_diproses'  => $diproses,
                'total_revenue'   => $totalRevenue,
                'success_rate'    => $total > 0 ? round(($success / $total) * 100, 1) : 0,
                'active_products' => $activeProducts,
                'low_stock_count' => $lowStockCount,
            ],
            'order_status_ratio' => [
                'success'   => $success,
                'cancelled' => $cancelled,
                'pending'   => $pending,
                'diproses' => $diproses,
            ],
            'top_couriers'  => $topCouriers,
            'top_products'  => $topProducts,
            'monthly_trend' => $monthlyTrend,
            'alerts' => [
                'low_stock'       => $lowStock,
                'pending_orders'  => $pendingOrders,
                'expiring_promos' => $expiringPromos,
            ],
        ]);
    }

    /**
     * GET /api/orders/pending-count
     */
    public function pendingCount()
    {
        $count = Cache::remember('orders_pending_count', 30, function () {
            return Order::where('status', 'pending')->count();
        });

        return response()->json([
            'count' => $count,
        ]);
    }

    public function showPublic($invoice_number)
    {
        $order = Order::with(['items', 'branch'])
            ->where('invoice_number', $invoice_number)
            ->firstOrFail();

        return response()->json([
            'data'  => $order,
            'store' => [
                'name'      => config('app.store_name', 'TB GROUP'),
                'address'   => config('app.store_address', ''),
                'phone'     => config('app.store_phone', ''),
            ],
        ]);
    }
}