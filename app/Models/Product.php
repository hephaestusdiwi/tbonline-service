<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    protected $fillable = [
        'name', 'slug', 'alternative_name', 'classification_id', 'category',
        'collections', 'brand', 'condition_id', 'sku', 'barcode',
        'buy_price', 'market_price', 'sell_price', 'pos_sell_price',
        'pos_sell_price_dynamic', 'comission', 'track_inventory',
        'uom', 'weight_kg', 'loyalty_points',
        'published', 'pos_hidden', 'description',
        'photo_1', 'photo_2', 'photo_3', 'photo_4', 'photo_5',
        'photo_6', 'photo_7', 'photo_8', 'photo_9', 'photo_10',
        'notes', 'tax_free_item',
    ];

    protected $casts = [
        'buy_price'              => 'float',
        'market_price'           => 'float',
        'sell_price'             => 'float',
        'pos_sell_price'         => 'float',
        'weight_kg'              => 'float',
        'comission'              => 'float',
        'published'              => 'boolean',
        'pos_hidden'             => 'boolean',
        'track_inventory'        => 'boolean',
        'pos_sell_price_dynamic' => 'boolean',
    ];

    // ─── Relasi ──────────────────────────────────────────────

    public function optionTypes(): HasMany
    {
        return $this->hasMany(ProductOptionType::class)->orderBy('position');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('position');
    }

    public function activeVariants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)
                    ->where('is_active', 1)
                    ->orderBy('position');
    }

    public function featuredProduct(): HasOne
    {
        return $this->hasOne(FeaturedProduct::class);
    }

    // ─── Helper ──────────────────────────────────────────────

    /** Apakah produk ini menggunakan sistem varian? */
    public function hasVariants(): bool
    {
        return $this->optionTypes()->exists();
    }

    /**
     * Harga terendah dari seluruh varian aktif.
     * Jika tidak ada varian, kembalikan sell_price produk.
     */
    public function getMinPriceAttribute(): float
    {
        if (! $this->hasVariants()) {
            return (float) $this->sell_price;
        }

        $min = $this->activeVariants()
                    ->whereNotNull('sell_price')
                    ->min('sell_price');

        return $min ?? (float) $this->sell_price;
    }

    /**
     * Total stok dari seluruh varian aktif.
     * Jika tidak ada varian, pakai stock_qty di product (bila ada kolom itu).
     */
    public function getTotalStockAttribute(): int
    {
        if (! $this->hasVariants()) {
            // fallback: jika masih ada kolom stock_qty di products
            return (int) ($this->attributes['stock_qty'] ?? 0);
        }

        return (int) $this->activeVariants()->sum('stock_qty');
    }

    // ─── Scope ───────────────────────────────────────────────

    public function scopePublished($query)
    {
        return $query->where('published', 1);
    }

    public function scopeTopSellers($query, int $limit = 10)
    {
        return $query->where('published', 1)
                    ->orderByDesc(
                        \App\Models\ProductVariant::selectRaw('COALESCE(SUM(qty_fast_moving), 0)')
                            ->whereColumn('product_id', 'products.id')
                    )
                    ->limit($limit);
    }

    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });

        static::updating(function (Product $product) {
            if ($product->isDirty('name') && empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    public static function findBySlug(string $slugWithId): self
    {
        // Extract ID dari bagian akhir, misal "sepatu-nike-1045" → 1045
        if (preg_match('/-(\d+)$/', $slugWithId, $m)) {
            return static::findOrFail((int) $m[1]);
        }

        // Fallback: kalau ternyata pure ID dikirim (backward compat)
        if (is_numeric($slugWithId)) {
            return static::findOrFail((int) $slugWithId);
        }

        abort(404, 'Produk tidak ditemukan.');
    }

    public function getRouteSlugAttribute(): string
    {
        $base = $this->slug ?: Str::slug($this->name);
        return $base . '-' . $this->id;
    }

    public function isFeatured(): bool
    {
        return $this->featuredProduct()->exists();
    }
}