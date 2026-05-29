<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id', 'label', 'sku', 'barcode',
        'buy_price', 'sell_price', 'pos_sell_price', 'market_price',
        'stock_qty', 'hold_qty', 'low_stock_alert', 'qty_fast_moving',
        'weight_kg', 'photo', 'is_active', 'position',
    ];

    protected $casts = [
        'buy_price'    => 'float',
        'sell_price'   => 'float',
        'pos_sell_price' => 'float',
        'market_price' => 'float',
        'weight_kg'    => 'float',
        'stock_qty'    => 'integer',
        'hold_qty'     => 'integer',
        'is_active'    => 'boolean',
    ];

    // ─── Relasi ──────────────────────────────────────────────

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function optionValues(): BelongsToMany
    {
        return $this->belongsToMany(
            ProductOptionValue::class,
            'product_variant_option_values'
        )->with('optionType');
    }

    // ─── Helper ──────────────────────────────────────────────

    /**
     * Regenerate label dari option values yang terpasang.
     * Panggil setelah sync optionValues.
     * Contoh output: "3mg" atau "Black / 500mAh"
     */
    public function regenerateLabel(): void
    {
        $this->loadMissing('optionValues.optionType');

        $label = $this->optionValues
            ->sortBy('optionType.position')
            ->pluck('value')
            ->implode(' / ');

        $this->update(['label' => $label ?: null]);
    }

    /**
     * Effective sell price: pakai harga varian jika ada,
     * fallback ke harga produk utama.
     */
    public function getEffectiveSellPriceAttribute(): float
    {
        return $this->sell_price ?? (float) $this->product->sell_price;
    }
}