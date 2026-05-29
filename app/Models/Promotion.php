<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Promotion extends Model
{
    protected $fillable = [
        'title',
        'image',
        'link',
        'link_type',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order'     => 'integer',
    ];

    protected $appends = ['image_url'];

    public function getImageUrlAttribute(): string 
    {
        return Storage::url($this->image);
    }
}
