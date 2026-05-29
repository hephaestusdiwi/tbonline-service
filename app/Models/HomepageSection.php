<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class HomepageSection extends Model
{
    protected $fillable = [
        'code',
        'type',
        'title',
        'payload',
        'position',
        'active',
        'start_at',
        'end_at',
    ];

    protected $casts = [
        'payload'   => 'array',
        'active'    => 'boolean',
        'start_at'  => 'datetime',
        'end_at'    => 'datetime',
    ];

    public function scopeVisible(Builder $query): Builder
    {
        $now = Carbon::now()->toDateTimeString();

        return $query
            ->where('active', true)
            ->where(fn ($q) => $q->whereNull('start_at')->orWhere('start_at', '<=', $now))
            ->where(fn ($q) => $q->whereNull('end_at')->orWhere('end_at', '>', $now))
            ->orderBy('position');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('position');
    }

    public function getScheduleStatusAttribute(): string
    {
        if (! $this->active) {
            return 'inactive';
        }

        $now = Carbon::now()->toDateTimeString(); // ← tambah toDateTimeString()

        if ($this->start_at && $this->start_at->isFuture()) {
            return 'scheduled';
        }

        if ($this->end_at && $this->end_at->isPast()) {
            return 'expired';
        }

        return 'active';
    }

    public function getIsLiveAttribute(): bool
    {
        return $this->schedule_status === 'active';
    }
}
