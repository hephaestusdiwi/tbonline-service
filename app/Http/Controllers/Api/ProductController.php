<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductOptionType;
use App\Models\ProductOptionValue;
use App\Models\ProductVariant;
use App\Services\ProductImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    // ═══════════════════════════════════════════════════════════
    // PRODUCTS (CRUD)
    // ═══════════════════════════════════════════════════════════

    /**
     * GET /api/products
     * List produk dengan filter lengkap.
     *
     * Query params:
     *   search, category, brand, collections, published, in_stock,
     *   has_discount, price_min, price_max,
     *   sort (price_asc|price_desc|name_asc|name_desc|discount|best_seller|newest),
     *   with_variants, per_page, page
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::query();

        // ── Search ──────────────────────────────────────────────
        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%$q%")
                    ->orWhere('sku', 'like', "%$q%")
                    ->orWhere('barcode', 'like', "%$q%")
                    ->orWhere('category', 'like', "%$q%")
                    ->orWhere('brand', 'like', "%$q%");
            });
        }

        // ── Filters ─────────────────────────────────────────────
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('brand')) {
            $query->where('brand', $request->brand);
        }

        if ($request->filled('collections')) {
            $query->where('collections', $request->collections);
        }

        if ($request->filled('published')) {
            $query->where('published', (int) $request->published);
        }

        if ($request->filled('in_stock')) {
            $query->where(function ($sub) {
                $sub->whereHas('variants', fn($v) => $v->where('stock_qty', '>', 0)->where('is_active', 1))
                    ->orWhereDoesntHave('variants');
            });
        }

        // Filter produk yang punya diskon (market_price > sell_price)
        if ($request->filled('has_discount')) {
            $query->whereColumn('market_price', '>', 'sell_price')
                  ->whereNotNull('market_price');
        }

        // Filter harga — pakai sell_price produk atau min harga variant
        if ($request->filled('price_min')) {
            $min = (float) $request->price_min;
            $query->where(function ($sub) use ($min) {
                $sub->where('sell_price', '>=', $min)
                    ->orWhereHas('variants', fn($v) => $v->where('sell_price', '>=', $min));
            });
        }

        if ($request->filled('price_max')) {
            $max = (float) $request->price_max;
            $query->where(function ($sub) use ($max) {
                $sub->where('sell_price', '<=', $max)
                    ->orWhereHas('variants', fn($v) => $v->where('sell_price', '<=', $max));
            });
        }

        // ── Sorting ─────────────────────────────────────────────
        switch ($request->input('sort')) {
            case 'price_asc':
                $query->orderBy('sell_price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('sell_price', 'desc');
                break;
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'discount':
                $query->orderByRaw('(COALESCE(market_price, 0) - sell_price) DESC');
                break;
            case 'best_seller':
                $query->orderByDesc(
                    ProductVariant::selectRaw('COALESCE(SUM(qty_fast_moving), 0)')
                        ->whereColumn('product_id', 'products.id')
                );
                break;
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        // ── Eager load ──────────────────────────────────────────
        if ($request->boolean('with_variants')) {
            $query->with([
                'optionTypes.values',
                'activeVariants.optionValues',
            ]);
        } else {
            $query->with([
                'activeVariants:id,product_id,label,sell_price,market_price,stock_qty,is_active',
            ]);
        }

        // ── Pagination ──────────────────────────────────────────
        if ($request->filled('per_page')) {
            $products = $query->paginate((int) $request->per_page);
        } else {
            $products = $query->get();
        }

        // ── Stats (query terpisah, tidak kena pagination) ────────
        // Ikut filter search & category supaya angka konsisten dengan tampilan,
        // tapi TIDAK kena filter published agar selalu tampil total sebenarnya.
        $statsQuery = Product::query();

        if ($request->filled('search')) {
            $q = $request->search;
            $statsQuery->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%$q%")
                    ->orWhere('sku', 'like', "%$q%")
                    ->orWhere('barcode', 'like', "%$q%")
                    ->orWhere('category', 'like', "%$q%")
                    ->orWhere('brand', 'like', "%$q%");
            });
        }

        if ($request->filled('category')) {
            $statsQuery->where('category', $request->category);
        }

        $stats = $statsQuery->selectRaw('
            COUNT(*) as total,
            SUM(published = 1) as published_count,
            COUNT(DISTINCT NULLIF(TRIM(category), "")) as category_count
        ')->first();

        // Low stock: produk yang minimal 1 varian aktifnya stock_qty <= low_stock_alert
        $lowStockCount = Product::whereHas('activeVariants', function ($q) {
            $q->whereRaw('stock_qty <= low_stock_alert');
        })->count();

        return response()->json([
            'data' => $products,
            'meta' => [
                'total'           => (int) ($stats->total ?? 0),
                'published_count' => (int) ($stats->published_count ?? 0),
                'low_stock_count' => (int) $lowStockCount,
                'category_count'  => (int) ($stats->category_count ?? 0),
            ],
        ]);
    }

    /**
     * GET /api/products/categories
     */
    public function categories(): JsonResponse
    {
        $categories = Product::where('published', 1)
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category')
            ->map(fn($c) => ['name' => $c]);

        return response()->json(['data' => $categories]);
    }

    /**
     * GET /api/products/brands
     */
    public function brands(): JsonResponse
    {
        $brands = Product::where('published', 1)
            ->whereNotNull('brand')
            ->where('brand', '!=', '')
            ->distinct()
            ->orderBy('brand')
            ->pluck('brand');

        return response()->json(['data' => $brands]);
    }

    /**
     * GET /api/products/collections
     */
    public function collections(): JsonResponse
    {
        $collections = Product::where('published', 1)
            ->whereNotNull('collections')
            ->where('collections', '!=', '')
            ->distinct()
            ->orderBy('collections')
            ->pluck('collections');

        return response()->json(['data' => $collections]);
    }

    /**
     * GET /api/products/{id}
     */
    public function show(int $id): JsonResponse
    {
        $product = Product::with([
            'optionTypes.values',
            'variants.optionValues.optionType',
        ])->findOrFail($id);

        return response()->json(['data' => $product]);
    }

    /**
     * POST /api/products
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name'                          => 'required|string|max:255',
            'sell_price'                    => 'required|numeric|min:0',
            'option_types'                  => 'nullable|array',
            'option_types.*.name'           => 'required_with:option_types|string|max:100',
            'option_types.*.values'         => 'required_with:option_types|array|min:1',
            'option_types.*.values.*'       => 'string|max:100',
            'variants'                      => 'nullable|array',
            'variants.*.option_value_indexes' => 'required_with:variants|array',
            'variants.*.sell_price'         => 'nullable|numeric|min:0',
            'variants.*.stock_qty'          => 'nullable|integer|min:0',
        ]);

        DB::beginTransaction();
        try {
            $product = Product::create($this->productFields($request));

            if ($request->filled('option_types')) {
                $this->syncOptionTypesAndVariants($product, $request);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal menyimpan: ' . $e->getMessage()], 500);
        }

        return response()->json([
            'data'    => $product->load(['optionTypes.values', 'variants.optionValues']),
            'message' => 'Produk berhasil ditambahkan',
        ], 201);
    }

    /**
     * PUT /api/products/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'option_types'                    => 'nullable|array',
            'option_types.*.name'             => 'required_with:option_types|string|max:100',
            'option_types.*.values'           => 'required_with:option_types|array|min:1',
            'variants'                        => 'nullable|array',
        ]);

        DB::beginTransaction();
        try {
            $product->update($this->productFields($request));

            if ($request->has('option_types')) {
                $product->optionTypes()->delete();
                $product->variants()->delete();

                if ($request->filled('option_types')) {
                    $this->syncOptionTypesAndVariants($product, $request);
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal update: ' . $e->getMessage()], 500);
        }

        return response()->json([
            'data'    => $product->load(['optionTypes.values', 'variants.optionValues']),
            'message' => 'Produk berhasil diperbarui',
        ]);
    }

    /**
     * DELETE /api/products/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        Product::findOrFail($id)->delete();

        return response()->json(['message' => 'Produk berhasil dihapus']);
    }

    /**
     * POST /api/products/bulk-delete
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'integer']);
        $deleted = Product::whereIn('id', $request->ids)->delete();

        return response()->json([
            'message' => "$deleted produk berhasil dihapus",
            'deleted' => $deleted,
        ]);
    }

    // ═══════════════════════════════════════════════════════════
    // VARIANTS
    // ═══════════════════════════════════════════════════════════

    public function variants(int $id): JsonResponse
    {
        $product = Product::with([
            'optionTypes.values',
            'variants.optionValues.optionType',
        ])->findOrFail($id);

        return response()->json([
            'product'      => $product->only(['id', 'name', 'sell_price']),
            'option_types' => $product->optionTypes,
            'variants'     => $product->variants,
        ]);
    }

    public function storeVariant(Request $request, int $id): JsonResponse
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'option_value_ids'   => 'required|array|min:1',
            'option_value_ids.*' => 'integer|exists:product_option_values,id',
            'sku'                => 'nullable|string|unique:product_variants,sku',
            'sell_price'         => 'nullable|numeric|min:0',
            'stock_qty'          => 'nullable|integer|min:0',
        ]);

        $variant = $product->variants()->create($this->variantFields($request));
        $variant->optionValues()->sync($request->option_value_ids);
        $variant->regenerateLabel();

        return response()->json([
            'data'    => $variant->load('optionValues.optionType'),
            'message' => 'Varian berhasil ditambahkan',
        ], 201);
    }

    public function updateVariant(Request $request, int $variantId): JsonResponse
    {
        $variant = ProductVariant::findOrFail($variantId);

        $request->validate([
            'option_value_ids'   => 'nullable|array',
            'option_value_ids.*' => 'integer|exists:product_option_values,id',
            'sku'                => 'nullable|string|unique:product_variants,sku,' . $variantId,
            'sell_price'         => 'nullable|numeric|min:0',
            'stock_qty'          => 'nullable|integer|min:0',
        ]);

        $variant->update($this->variantFields($request));

        if ($request->has('option_value_ids')) {
            $variant->optionValues()->sync($request->option_value_ids);
            $variant->regenerateLabel();
        }

        return response()->json([
            'data'    => $variant->load('optionValues.optionType'),
            'message' => 'Varian berhasil diperbarui',
        ]);
    }

    public function destroyVariant(int $variantId): JsonResponse
    {
        ProductVariant::findOrFail($variantId)->delete();

        return response()->json(['message' => 'Varian berhasil dihapus']);
    }

    // ═══════════════════════════════════════════════════════════
    // SEARCH
    // ═══════════════════════════════════════════════════════════

    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q'     => 'required|string|min:2|max:100',
            'limit' => 'sometimes|integer|min:1|max:20',
        ]);

        $q     = trim($request->input('q'));
        $limit = (int) $request->input('limit', 6);

        $products = Product::query()
            ->where('published', 1)
            ->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('alternative_name', 'like', "%{$q}%")
                    ->orWhere('category', 'like', "%{$q}%")
                    ->orWhere('brand', 'like', "%{$q}%")
                    ->orWhere('sku', 'like', "%{$q}%");
            })
            ->orderByRaw('CASE WHEN name LIKE ? THEN 0 ELSE 1 END', ["{$q}%"])
            ->orderBy('name')
            ->limit($limit)
            ->with(['activeVariants' => fn($q) => $q->select(
                'id', 'product_id', 'label', 'sell_price', 'stock_qty', 'photo'
            )])
            ->get(['id', 'name', 'alternative_name', 'category', 'brand', 'sell_price', 'photo_1']);

        return response()->json([
            'data'  => $products,
            'query' => $q,
            'count' => $products->count(),
        ]);
    }

    // ═══════════════════════════════════════════════════════════
    // IMPORT
    // ═══════════════════════════════════════════════════════════

    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'mode'     => 'required|in:skip,update,replace',
            'products' => 'required|array|min:1',
        ]);

        $mode     = $request->mode;
        $rows     = $request->products;
        $imported = 0;
        $updated  = 0;
        $skipped  = 0;
        $failed   = 0;
        $errors   = [];

        DB::beginTransaction();
        try {
            if ($mode === 'replace') {
                Product::truncate();
            }

            foreach ($rows as $i => $row) {
                $rowNum = $i + 2;

                if (empty($row['name'])) {
                    $errors[] = "Baris $rowNum: kolom 'name' wajib diisi";
                    $failed++;
                    continue;
                }

                $data = $this->mapImportRow($row);

                if ($mode === 'skip' || $mode === 'replace') {
                    $exists = Product::whereRaw('LOWER(name) = ?', [strtolower($data['name'])])->exists();
                    if ($exists && $mode === 'skip') {
                        $skipped++;
                        continue;
                    }
                    $product = Product::create($data);
                    $this->importVariants($product, $row);
                    $imported++;
                } elseif ($mode === 'update') {
                    $product = Product::whereRaw('LOWER(name) = ?', [strtolower($data['name'])])->first();
                    if ($product) {
                        $product->update($data);
                        $updated++;
                    } else {
                        $product = Product::create($data);
                        $this->importVariants($product, $row);
                        $imported++;
                    }
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Import gagal: ' . $e->getMessage()], 500);
        }

        return response()->json([
            'imported' => $imported,
            'updated'  => $updated,
            'skipped'  => $skipped,
            'failed'   => $failed,
            'errors'   => $errors,
            'message'  => "Import selesai: $imported ditambah, $updated diperbarui, $skipped dilewati, $failed gagal.",
        ]);
    }

    // ═══════════════════════════════════════════════════════════
    // IMPORT OLSERA
    // ═══════════════════════════════════════════════════════════

    /**
     * POST /api/products/import-olsera
     */
    public function importOlsera(Request $request): JsonResponse
    {
        set_time_limit(600);

        $request->validate([
            'mode'     => 'required|in:skip,update,replace',
            'products' => 'required|array|min:1',
        ]);

        $mode         = $request->mode;
        $rows         = $request->products;
        $imageService = app(ProductImageService::class);

        $groups = [];
        foreach ($rows as $row) {
            $name = trim($row['name'] ?? '');
            if ($name === '') continue;
            $key = strtolower($name);
            if (! isset($groups[$key])) {
                $groups[$key] = ['meta' => $row, 'variants' => []];
            }
            $variantName = trim($row['variant_names'] ?? '');
            if ($variantName !== '') {
                $groups[$key]['variants'][] = [
                    'label'           => $variantName,
                    'variant_label'   => trim($row['variant_label'] ?? ''),
                    'sku'             => $row['sku'] ?? null,
                    'barcode'         => $row['barcode'] ?? null,
                    'sell_price'      => is_numeric($row['sell_price'] ?? null) ? (float) $row['sell_price'] : null,
                    'buy_price'       => is_numeric($row['buy_price'] ?? null) ? (float) $row['buy_price'] : null,
                    'market_price'    => is_numeric($row['market_price'] ?? null) ? (float) $row['market_price'] : null,
                    'pos_sell_price'  => is_numeric($row['pos_sell_price'] ?? null) ? (float) $row['pos_sell_price'] : null,
                    'stock_qty'       => (int) ($row['stock_qty'] ?? 0),
                    'hold_qty'        => (int) ($row['hold_qty'] ?? 0),
                    'low_stock_alert' => (int) ($row['low_stock_alert'] ?? 2),
                    'qty_fast_moving' => (int) ($row['qty_fast_moving'] ?? 0),
                    'weight_kg'       => is_numeric($row['weight_kg'] ?? null) ? (float) $row['weight_kg'] : null,
                ];
            }
        }

        $imported = 0; $updated = 0; $skipped = 0; $failed = 0; $errors = [];

        if ($mode === 'replace') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            Product::truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        DB::beginTransaction();
        try {
            foreach ($groups as $key => $group) {
                $meta     = $group['meta'];
                $variants = $group['variants'];
                $name     = trim($meta['name']);

                try {
                    $productData = $this->mapOlseraRow($meta);

                    $subfolder   = 'olsera/' . \Illuminate\Support\Str::slug($name);
                    $productData = array_merge(
                        $productData,
                        $imageService->downloadProductPhotos(
                            $this->extractPhotoFields($productData),
                            $subfolder,
                        ),
                    );

                    if ($mode === 'skip') {
                        $exists = Product::whereRaw('LOWER(name) = ?', [$key])->exists();
                        if ($exists) { $skipped++; continue; }
                        $product = Product::create($productData);
                        $this->createVariantsFromOlsera($product, $variants);
                        $imported++;

                    } elseif ($mode === 'replace') {
                        $product = Product::create($productData);
                        $this->createVariantsFromOlsera($product, $variants);
                        $imported++;

                    } elseif ($mode === 'update') {
                        $product = Product::whereRaw('LOWER(name) = ?', [$key])->first();
                        if ($product) {
                            $product->update($productData);
                            $product->optionTypes()->delete();
                            $product->variants()->delete();
                            $this->createVariantsFromOlsera($product, $variants);
                            $updated++;
                        } else {
                            $product = Product::create($productData);
                            $this->createVariantsFromOlsera($product, $variants);
                            $imported++;
                        }
                    }

                } catch (\Throwable $e) {
                    $errors[] = "Produk '$name': " . $e->getMessage();
                    $failed++;
                }
            }
            DB::commit();

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Import gagal: ' . $e->getMessage()], 500);
        }

        return response()->json([
            'imported' => $imported, 'updated'  => $updated,
            'skipped'  => $skipped,  'failed'   => $failed,
            'errors'   => $errors,
            'message'  => "Import Olsera selesai: $imported ditambah, $updated diperbarui, $skipped dilewati, $failed gagal.",
        ]);
    }

    private function createVariantsFromOlsera(Product $product, array $variants): void
    {
        if (empty($variants)) return;

        $optionTypeName = $variants[0]['variant_label'] ?: 'Varian';
        $optionType     = $product->optionTypes()->create(['name' => $optionTypeName, 'position' => 0]);

        foreach ($variants as $pos => $v) {
            $optionValue = $optionType->values()->create(['value' => $v['label'], 'position' => $pos]);

            $variant = $product->variants()->create([
                'label'           => $v['label'],
                'sku'             => $v['sku'] ?: null,
                'barcode'         => $v['barcode'] ?: null,
                'buy_price'       => $v['buy_price'],
                'sell_price'      => $v['sell_price'],
                'pos_sell_price'  => $v['pos_sell_price'],
                'market_price'    => $v['market_price'],
                'stock_qty'       => $v['stock_qty'],
                'hold_qty'        => $v['hold_qty'],
                'low_stock_alert' => $v['low_stock_alert'],
                'qty_fast_moving' => $v['qty_fast_moving'],
                'weight_kg'       => $v['weight_kg'],
                'photo'           => null,
                'is_active'       => 1,
                'position'        => $pos,
            ]);

            $variant->optionValues()->sync([$optionValue->id]);
        }
    }

    // ═══════════════════════════════════════════════════════════
    // PRIVATE HELPERS
    // ═══════════════════════════════════════════════════════════

    private function productFields(Request $request): array
    {
        return $request->only([
            'name', 'alternative_name', 'classification_id', 'category',
            'collections', 'brand', 'condition_id', 'sku', 'barcode',
            'buy_price', 'market_price', 'sell_price', 'pos_sell_price',
            'pos_sell_price_dynamic', 'comission', 'track_inventory',
            'uom', 'weight_kg', 'loyalty_points',
            'published', 'pos_hidden', 'description',
            'photo_1', 'photo_2', 'photo_3', 'photo_4', 'photo_5',
            'photo_6', 'photo_7', 'photo_8', 'photo_9', 'photo_10',
            'notes', 'tax_free_item',
        ]);
    }

    private function variantFields(Request $request): array
    {
        return $request->only([
            'sku', 'barcode', 'buy_price', 'sell_price', 'pos_sell_price',
            'market_price', 'stock_qty', 'hold_qty', 'low_stock_alert',
            'qty_fast_moving', 'weight_kg', 'photo', 'is_active', 'position',
        ]);
    }

    private function syncOptionTypesAndVariants(Product $product, Request $request): void
    {
        $valueIdMap = [];

        foreach ($request->option_types as $typeIdx => $typeData) {
            $optionType = $product->optionTypes()->create([
                'name'     => $typeData['name'],
                'position' => $typeData['position'] ?? $typeIdx,
            ]);

            foreach ($typeData['values'] as $valIdx => $valText) {
                $optionValue = $optionType->values()->create([
                    'value'    => $valText,
                    'position' => $valIdx,
                ]);
                $valueIdMap[$typeIdx][$valIdx] = $optionValue->id;
            }
        }

        if (! $request->filled('variants')) {
            return;
        }

        foreach ($request->variants as $pos => $varData) {
            $variant = $product->variants()->create([
                'sku'             => $varData['sku'] ?? null,
                'barcode'         => $varData['barcode'] ?? null,
                'buy_price'       => $varData['buy_price'] ?? null,
                'sell_price'      => $varData['sell_price'] ?? null,
                'pos_sell_price'  => $varData['pos_sell_price'] ?? null,
                'market_price'    => $varData['market_price'] ?? null,
                'stock_qty'       => $varData['stock_qty'] ?? 0,
                'hold_qty'        => $varData['hold_qty'] ?? 0,
                'low_stock_alert' => $varData['low_stock_alert'] ?? 2,
                'weight_kg'       => $varData['weight_kg'] ?? null,
                'photo'           => $varData['photo'] ?? null,
                'is_active'       => $varData['is_active'] ?? 1,
                'position'        => $varData['position'] ?? $pos,
            ]);

            $pivotIds = [];
            foreach ($varData['option_value_indexes'] as $typeIdx => $valIdx) {
                if (isset($valueIdMap[$typeIdx][$valIdx])) {
                    $pivotIds[] = $valueIdMap[$typeIdx][$valIdx];
                }
            }
            $variant->optionValues()->sync($pivotIds);
            $variant->regenerateLabel();
        }
    }

    private function importVariants(Product $product, array $row): void
    {
        $optionsRaw = $row['variant_options'] ?? null;
        if (empty($optionsRaw)) return;

        $optionTypes = json_decode($optionsRaw, true);
        if (! is_array($optionTypes)) return;

        $skuMap   = $this->parseLabelMap($row['variant_skus']   ?? '');
        $priceMap = $this->parseLabelMap($row['variant_prices'] ?? '');
        $stockMap = $this->parseLabelMap($row['variant_stocks'] ?? '');

        $createdTypes = [];
        foreach ($optionTypes as $pos => $typeData) {
            $type   = $product->optionTypes()->create(['name' => $typeData['type'], 'position' => $pos]);
            $values = [];
            foreach ($typeData['values'] as $vi => $val) {
                $values[] = $type->values()->create(['value' => $val, 'position' => $vi]);
            }
            $createdTypes[] = ['type' => $type, 'values' => $values];
        }

        $combinations = [[]];
        foreach ($createdTypes as $typeData) {
            $newCombinations = [];
            foreach ($combinations as $existing) {
                foreach ($typeData['values'] as $val) {
                    $newCombinations[] = array_merge($existing, [$val]);
                }
            }
            $combinations = $newCombinations;
        }

        foreach ($combinations as $pos => $combo) {
            $label   = collect($combo)->pluck('value')->implode(' / ');
            $variant = $product->variants()->create([
                'label'      => $label,
                'sku'        => $skuMap[$label]    ?? null,
                'sell_price' => isset($priceMap[$label]) ? (float) $priceMap[$label] : null,
                'stock_qty'  => (int) ($stockMap[$label] ?? 0),
                'is_active'  => 1,
                'position'   => $pos,
            ]);
            $variant->optionValues()->sync(collect($combo)->pluck('id')->toArray());
        }
    }

    private function parseLabelMap(string $raw): array
    {
        if (empty(trim($raw))) return [];
        $map = [];
        foreach (explode(',', $raw) as $pair) {
            [$key, $val] = array_pad(explode(':', $pair, 2), 2, '');
            $map[trim($key)] = trim($val);
        }
        return $map;
    }

    private function mapImportRow(array $row): array
    {
        $toBool = fn($val) => in_array(strtolower((string) $val), ['1', 'yes', 'true', 'y']) ? 1 : 0;

        return [
            'name'                   => trim($row['name'] ?? ''),
            'alternative_name'       => $row['alternative_name'] ?? null,
            'classification_id'      => $row['classification_id'] ?? null,
            'category'               => $row['category'] ?? null,
            'collections'            => $row['collections'] ?? null,
            'brand'                  => $row['brand'] ?? null,
            'condition_id'           => $row['condition_id'] ?? 'N',
            'sku'                    => $row['sku'] ?? null,
            'barcode'                => $row['barcode'] ?? null,
            'buy_price'              => is_numeric($row['buy_price'] ?? null) ? (float) $row['buy_price'] : null,
            'market_price'           => is_numeric($row['market_price'] ?? null) ? (float) $row['market_price'] : null,
            'sell_price'             => is_numeric($row['sell_price'] ?? null) ? (float) $row['sell_price'] : 0,
            'pos_sell_price'         => is_numeric($row['pos_sell_price'] ?? null) ? (float) $row['pos_sell_price'] : null,
            'pos_sell_price_dynamic' => (int) ($row['pos_sell_price_dynamic'] ?? 0),
            'comission'              => (float) ($row['comission'] ?? 0),
            'track_inventory'        => (int) ($row['track_inventory'] ?? 1),
            'uom'                    => $row['uom'] ?? null,
            'weight_kg'              => is_numeric($row['weight_kg'] ?? null) ? (float) $row['weight_kg'] : null,
            'loyalty_points'         => (int) ($row['loyalty_points'] ?? 0),
            'published'              => $toBool($row['published'] ?? 1),
            'pos_hidden'             => $toBool($row['pos_hidden'] ?? 0),
            'description'            => $row['description'] ?? null,
            'photo_1'                => $row['photo_1'] ?? null,
            'photo_2'                => $row['photo_2'] ?? null,
            'photo_3'                => $row['photo_3'] ?? null,
            'photo_4'                => $row['photo_4'] ?? null,
            'photo_5'                => $row['photo_5'] ?? null,
            'photo_6'                => $row['photo_6'] ?? null,
            'photo_7'                => $row['photo_7'] ?? null,
            'photo_8'                => $row['photo_8'] ?? null,
            'photo_9'                => $row['photo_9'] ?? null,
            'photo_10'               => $row['photo_10'] ?? null,
            'notes'                  => $row['notes'] ?? null,
            'tax_free_item'          => $row['tax_free_item'] ?? 'No',
        ];
    }

    private function mapOlseraRow(array $row): array
    {
        $toBool = fn($val) => in_array(strtolower((string) $val), ['1', 'yes', 'true', 'y']) ? 1 : 0;
        return [
            'name'                   => trim($row['name'] ?? ''),
            'alternative_name'       => $row['alternative_name'] ?? null,
            'classification_id'      => $row['classification_id'] ?? null,
            'category'               => $row['category'] ?? null,
            'collections'            => $row['collections'] ?? null,
            'brand'                  => $row['brand'] ?? null,
            'condition_id'           => $row['condition_id'] ?? 'N',
            'sku'                    => $row['sku'] ?? null,
            'barcode'                => $row['barcode'] ?? null,
            'buy_price'              => is_numeric($row['buy_price'] ?? null) ? (float) $row['buy_price'] : null,
            'market_price'           => is_numeric($row['market_price'] ?? null) ? (float) $row['market_price'] : null,
            'sell_price'             => is_numeric($row['sell_price'] ?? null) ? (float) $row['sell_price'] : 0,
            'pos_sell_price'         => is_numeric($row['pos_sell_price'] ?? null) ? (float) $row['pos_sell_price'] : null,
            'pos_sell_price_dynamic' => (int) ($row['pos_sell_price_dynamic'] ?? 0),
            'comission'              => (float) ($row['comission'] ?? 0),
            'track_inventory'        => (int) ($row['track_inventory'] ?? 1),
            'uom'                    => $row['uom'] ?? null,
            'weight_kg'              => is_numeric($row['weight_kg'] ?? null) ? (float) $row['weight_kg'] : null,
            'loyalty_points'         => (int) ($row['loyalty_points'] ?? 0),
            'published'              => $toBool($row['published'] ?? 1),
            'pos_hidden'             => $toBool($row['pos_hidden'] ?? 0),
            'description'            => $row['description'] ?? null,
            'photo_1'                => $row['photo_1'] ?? null,
            'photo_2'                => $row['photo_2'] ?? null,
            'photo_3'                => $row['photo_3'] ?? null,
            'photo_4'                => $row['photo_4'] ?? null,
            'photo_5'                => $row['photo_5'] ?? null,
            'photo_6'                => $row['photo_6'] ?? null,
            'photo_7'                => $row['photo_7'] ?? null,
            'photo_8'                => $row['photo_8'] ?? null,
            'photo_9'                => $row['photo_9'] ?? null,
            'photo_10'               => $row['photo_10'] ?? null,
            'notes'                  => $row['notes'] ?? null,
            'tax_free_item'          => $row['tax_free_item'] ?? 'No',
        ];
    }

    private function extractPhotoFields(array $data): array
    {
        $fields = [];
        for ($i = 1; $i <= 10; $i++) {
            $key = "photo_{$i}";
            if (! empty($data[$key])) {
                $fields[$key] = $data[$key];
            }
        }
        return $fields;
    }
}