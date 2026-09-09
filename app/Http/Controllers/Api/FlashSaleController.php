<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FlashSale;
use App\Models\FlashSaleProduct;
use App\Models\Product;
use Illuminate\Http\Request;

class FlashSaleController extends Controller
{
    // GET /api/flash-sale — publik, dipakai homepage & admin panel sekaligus.
    public function index()
    {
        $flashSale = FlashSale::first();

        $products = FlashSaleProduct::with('product')
            ->orderBy('order')
            ->get()
            ->filter(fn ($fsp) => $fsp->product && $fsp->product->published)
            ->map(fn ($fsp) => [
                'id'          => $fsp->id,
                'product_id'  => $fsp->product_id,
                'name'        => $fsp->product->name,
                'photo'       => $fsp->product->photo_1,
                'sell_price'  => $fsp->product->sell_price,
                'market_price'=> $fsp->product->market_price,
                'flash_price' => $fsp->flash_price,
                'stock_qty'   => $fsp->product->stock_qty,
                'order'       => $fsp->order,
            ])
            ->values();

        return response()->json([
            'is_active' => $flashSale->is_active ?? false,
            'ends_at'   => $flashSale->ends_at,
            'products'  => $products,
        ]);
    }

    // PUT /api/flash-sale/settings
    public function updateSettings(Request $request)
    {
        $request->validate([
            'is_active' => 'required|boolean',
            'ends_at'   => 'nullable|date',
        ]);

        $flashSale = FlashSale::first() ?? FlashSale::create(['is_active' => false]);
        $flashSale->update([
            'is_active' => $request->is_active,
            'ends_at'   => $request->ends_at,
        ]);

        return response()->json($flashSale->fresh());
    }

    // PUT /api/flash-sale/products — replace seluruh daftar produk flash sale.
    public function syncProducts(Request $request)
    {
        $request->validate([
            'products'               => 'present|array',
            'products.*.product_id'  => 'required|exists:products,id',
            'products.*.flash_price' => 'nullable|numeric|min:0',
            'products.*.order'       => 'required|integer',
        ]);

        FlashSaleProduct::query()->delete();

        foreach ($request->products as $item) {
            FlashSaleProduct::create([
                'product_id'  => $item['product_id'],
                'flash_price' => $item['flash_price'] ?? null,
                'order'       => $item['order'],
            ]);
        }

        return response()->json(['message' => 'Daftar produk flash sale berhasil disimpan']);
    }

    // GET /api/flash-sale/products/search — admin, buat cari produk yang mau ditambahin.
    public function searchProducts(Request $request)
    {
        $q = trim($request->get('q', ''));

        $products = Product::query()
            ->where('published', true)
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('sku', 'like', "%{$q}%");
                });
            })
            ->select('id', 'name', 'sku', 'photo_1', 'sell_price', 'market_price', 'stock_qty')
            ->limit(20)
            ->get();

        return response()->json($products);
    }
}