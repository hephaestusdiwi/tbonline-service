<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class PromoCode extends Model
{
    protected $fillable = [
        'code', 'description', 'discount_type', 'discount_value',
        'min_purchase', 'max_usage', 'used_count', 'is_active', 'expired_at',
        'show_popup', 'popup_label',
    ];

    protected $casts = [
        'expired_at' => 'datetime',
        'show_popup'  => 'boolean',
        'is_active'  => 'boolean',
    ];

    /**
     * Validasi apakah kode promo bisa dipakai.
     * Mengembalikan ['valid' => bool, 'message' => string]
     */
    public function isValid(int $subtotal, ?string $customerPhone): array
    {
        if (!$this->is_active) {
            return ['valid' => false, 'message' => 'Kode promo tidak aktif'];
        }

        if ($this->expired_at && Carbon::now()->isAfter($this->expired_at)) {
            return ['valid' => false, 'message' => 'Kode promo sudah kadaluwarsa'];
        }

        if ($this->max_usage !== null && $this->used_count >= $this->max_usage) {
            return ['valid' => false, 'message' => 'Kode promo sudah mencapai batas pemakaian'];
        }

        if ($subtotal < $this->min_purchase) {
            return [
                'valid'   => false,
                'message' => 'Minimum pembelian Rp ' . number_format($this->min_purchase, 0, ',', '.') . ' untuk menggunakan kode ini.',
            ];
        }

        // Cek per nomor HP hanya jika phone tersedia
        if ($customerPhone) {
            $alreadyUsed = Order::where('customer_phone', $customerPhone)
                ->where('promo_code', $this->code)
                ->whereIn('status', ['pending', 'success'])
                ->exists();

            if ($alreadyUsed) {
                return ['valid' => false, 'message' => 'Nomor HP ini sudah pernah menggunakan kode promo ini'];
            }
        }

        return ['valid' => true, 'message' => 'Kode promo berhasil digunakan'];
    }

    /**
     * Hitung besaran diskon berdasarkan type.
     */
    public function calculateDiscount(int $subtotal, int $shippingCost): int
    {
        return match ($this->discount_type) {
            'percentage'        => (int) round($subtotal * ($this->discount_value / 100)),
            'fixed'             => (int) min($this->discount_value, $subtotal),
            'free_shipping'     => $shippingCost,
            default             => 0,
        };
    }
}
