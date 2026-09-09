<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    protected $fillable = [
        'title',
        'type',
        'file_path',
        'file_path_mobile',
        'order',
        'is_active',
        'is_processing'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_processing' => 'boolean',
    ];

    // Accessor to get the full URL file
    public function getFileUrlAttribute(): string 
    {
        return asset('storage/' . $this->file_path);
    }

    // Aset khusus mobile — null kalau admin belum upload (frontend yang fallback
    // ke file_url, bukan di sini, supaya form admin bisa bedain "belum diisi"
    // vs "sengaja sama dengan desktop").
    public function getFileUrlMobileAttribute(): ?string
    {
        return $this->file_path_mobile ? asset('storage/' . $this->file_path_mobile) : null;
    }

    protected $appends = ['file_url', 'file_url_mobile'];
}