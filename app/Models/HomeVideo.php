<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeVideo extends Model
{
    protected $fillable = [
        'slot',
        'video_path',
        'title',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getVideoUrlAttribute(): ?string
    {
        return $this->video_path ? asset('storage/' . $this->video_path) : null;
    }

    protected $appends = ['video_url'];
}