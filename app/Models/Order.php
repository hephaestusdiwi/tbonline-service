<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    protected $fillable = [
        'invoice_number', 'customer_name', 'customer_phone', 'customer_email',
        'address', 'subdistrict', 'district', 'city', 'province',
        'postal_code', 'destination_id',
        'shipping_courier', 'shipping_service', 'shipping_name',
        'shipping_cost', 'shipping_etd',
        'subtotal', 'discount_amount', 'promo_code', 'total_price',
        'notes', 'status', 'cancel_reason',
        'fulfillment_type', 'branch_id', 
        'revised_at', 'revised_by', 'revision_count',
        'shipping_is_custom', 'shipping_custom_note',
        'confirmed_by', 'confirmed_at',
    ];

    protected $casts = [
        'shipping_cost'   => 'integer',
        'subtotal'        => 'integer',
        'discount_amount' => 'integer',
        'total_price'     => 'integer',
        'revision_count'  => 'integer',
        'revised_at'      => 'datetime',
        'shipping_is_custom' => 'boolean',
        'confirmed_at'    => 'datetime',
    ];

    // Status yang boleh direvisi
    const STATUSES                  = ['pending', 'diproses', 'success', 'cancelled'];
    const FINAL_STATUSES            = ['success', 'cancelled'];
    const REVISABLE_STATUSES        = ['pending', 'diproses'];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function branch()
    {
        return $this->belongsTo(\App\Models\Branch::class);
    }

    public function revisor()
    {
        return $this->belongsTo(\App\Models\User::class, 'revised_by');
    }

    public function revisions()
    {
        return $this->hasMany(OrderRevision::class);
    }

    public function isRevisable(): bool
    {
        return in_array($this->status, self::REVISABLE_STATUSES);
    }

    public function isFinal(): bool
    {
        return in_array($this->status, self::FINAL_STATUSES);
    }

    public function confirmer()
    {
        return $this->belongsTo(\App\Models\User::class, 'confirmed_by');
    }

    public static function generateInvoiceNumber(): string
    {
        $date   = now()->format('Ymd');
        $prefix = "INV{$date}";

        $last = DB::table('orders')
            ->where('invoice_number', 'like', "{$prefix}%")
            ->lockForUpdate()
            ->orderByDesc('invoice_number')
            ->value('invoice_number');

        $next      = $last ? (intval(substr($last, -3)) + 1) : 1;
        $candidate = $prefix . str_pad($next, 3, '0', STR_PAD_LEFT);

        // Guard: loop jika ternyata candidate sudah ada (extra safety)
        while (DB::table('orders')->where('invoice_number', $candidate)->exists()) {
            $next++;
            $candidate = $prefix . str_pad($next, 3, '0', STR_PAD_LEFT);
        }

        return $candidate;
    }
}