<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FlashSale extends Model
{
    protected $fillable = [
        'is_active',
        'ends_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'ends_at' => 'datetime',
    ];
}