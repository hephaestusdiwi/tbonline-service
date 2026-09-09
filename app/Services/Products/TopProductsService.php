<?php

namespace App\Services\Products;

use App\Models\FeaturedProduct;
use App\Models\Product;
use Illuminate\Support\Collection;

class TopProductsService
{
    /**
     * Ambil produk untuk homepage.
     *
     * Maksimal 6 produk.
     *
     * Prioritas:
     * 1. Featured products dari admin
     * 2. Jika jumlah featured kurang dari 6,
     *    slot sisanya diisi oleh produk terlaris
     *
     * Produk yang sudah menjadi featured tidak akan
     * muncul lagi sebagai produk terlaris.
     */
    public function getHomepageProducts(int $total = 6): Collection
    {
        $featured = FeaturedProduct::active()
            ->with('product.optionTypes')
            ->get()
            ->pluck('product')
            ->filter();

        $featuredIds = $featured->pluck('id');
        $remaining = $total - $featured->count();

        if ($remaining <= 0) {
            return $featured->take($total);
        }

        $auto = Product::topSellers($remaining)
            ->whereNotIn('id', $featuredIds)
            ->with('optionTypes')
            ->get();

        return $featured
            ->concat($auto)
            ->take($total);
    }

    /**
     * Set/replace semua featured products sekaligus.
     *
     * $productIds = array of product IDs
     * Urutan array akan menjadi sort_order.
     */
    public function setFeaturedProducts(array $productIds): void
    {
        $existing = Product::whereIn('id', $productIds)
            ->pluck('id');

        \DB::transaction(function () use ($productIds, $existing) {
            FeaturedProduct::query()->delete();

            $inserts = collect($productIds)
                ->filter(fn($id) => $existing->contains($id))
                ->values()
                ->map(fn($id, $index) => [
                    'product_id' => $id,
                    'sort_order' => $index,
                    'is_active'  => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
                ->all();

            FeaturedProduct::insert($inserts);
        });
    }

    /**
     * Reorder featured products.
     *
     * $orderedIds berisi ID dari tabel featured_products,
     * bukan product_id.
     */
    public function reorderFeatured(array $orderedIds): void
    {
        \DB::transaction(function () use ($orderedIds) {
            foreach ($orderedIds as $index => $id) {
                FeaturedProduct::where('id', $id)
                    ->update([
                        'sort_order' => $index,
                    ]);
            }
        });
    }

    /**
     * Ambil daftar featured products untuk halaman admin.
     */
    public function getFeaturedList(): Collection
    {
        return FeaturedProduct::active()
            ->with('product.optionTypes')
            ->get();
    }
}