<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitorLog extends Model
{
    protected $fillable = [
        'session_id', 'ip_address', 'user_id',
        'page', 'page_title', 'referrer', 'referrer_source',
        'user_agent', 'browser', 'browser_version', 'os', 'device_type', 'device_name',
        'country', 'country_code', 'city', 'region', 'latitude', 'longitude',
        'time_on_page', 'is_bounce', 'is_new_visitor',
        'visited_at',
    ];

    protected $casts = [
        'is_bounce'         => 'boolean',
        'is_new_visitor'    => 'boolean',
        'visited_at'        => 'datetime',
        'time_on_page'      => 'integer',
        'latitude'          => 'float',
        'longitude'         => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeInRange($query, $from, $to)
    {
        return $query->whereBetween('visited_at', [$from, $to]);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('visited_at', today());
    }
}
