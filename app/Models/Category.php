<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'name',
        'product_categories',
        'photo_path',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active'           => 'boolean',
        'product_categories'  => 'array',
    ];

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo_path ? asset('storage/' . $this->photo_path) : null;
    }

    protected $appends = ['photo_url'];
}