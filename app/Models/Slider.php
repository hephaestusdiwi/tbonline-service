<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    protected $fillable = [
        'title',
        'type',
        'file_path',
        'order',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Accessor to get the full URL file
    public function getFileUrlAttribute(): string 
    {
        return asset('storage/' . $this->file_path);
    }

    protected $appends = ['file_url'];
}
