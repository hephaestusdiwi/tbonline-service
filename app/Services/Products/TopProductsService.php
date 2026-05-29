<?php

namespace App\Services\Products;

use App\Models\FeaturedProduct;
use App\Models\Product;
use Illuminate\Support\Collection;

class TopProductsService
{
    /**
     * Ambil top products untuk homepage.
     * Manual featured products tampil duluan, sisanya auto dari terlaris.
     */
    public function getHomepageProducts(int $total = 5): Collection
    {
        $featured = FeaturedProduct::active()
            ->with('product.optionTypes')
            ->get()
            ->pluck('product')
            ->filter();

        $featuredIds = $featured->pluck('id');
        $remaining   = $total - $featured->count();

        $auto = collect();
        if ($remaining > 0) {
            $auto = Product::topSellers($remaining)
                ->whereNotIn('id', $featuredIds)
                ->with('optionTypes')
                ->get();
        }

        return $featured->concat($auto)->take($total);
    }

    /**
     * Set/replace semua featured products sekaligus.
     * $productIds = array of product IDs, urutan = sort_order.
     */
    public function setFeaturedProducts(array $productIds): void
    {
        $existing = Product::whereIn('id', $productIds)->pluck('id');

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

    public function reorderFeatured(array $orderedIds): void
    {
        \DB::transaction(function () use ($orderedIds) {
            foreach ($orderedIds as $index => $id) {
                FeaturedProduct::where('id', $id)
                    ->update(['sort_order' => $index]);
            }
        });
    }

    public function getFeaturedList(): Collection
    {
        return FeaturedProduct::active()->with('product.optionTypes')->get();
    }
}