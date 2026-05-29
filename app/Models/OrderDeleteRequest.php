<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDeleteRequest extends Model
{
    protected $fillable = [
        'order_id',
        'requested_by',
        'reason',
        'status',
        'reviewed_by',
        'review_note',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewer_at' => 'datetime',
    ];

    // Relasi
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
