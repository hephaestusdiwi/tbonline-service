<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Branch extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'address',
        'city',
        'province',
        'instagram',
        'google_maps_url',
        'operating_hours',
        'latitude',
        'longitude',
        'is_active',
    ];

    protected $casts = [
        'operating_hours' => 'array',
        'is_active'       => 'boolean',
        'latitude'        => 'float',
        'longitude'       => 'float',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('city', 'like', "%{$search}%")
              ->orWhere('province', 'like', "%{$search}%")
              ->orWhere('address', 'like', "%{$search}%");
        });
    }

    protected function province(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? ucwords(strtolower($value)) : null,
        );
    }

    protected function city(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? ucwords(strtolower($value)) : null,
        );
    }
}
