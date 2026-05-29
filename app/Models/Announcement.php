<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model 
{
    protected $fillable = [
        'text',
        'link_url',
        'link_label',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
