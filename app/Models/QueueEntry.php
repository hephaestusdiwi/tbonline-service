<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QueueEntry extends Model
{
    protected $fillable = [
        'session_id',
        'position',
        'status',
        'estimated_wait_seconds',
        'joined_at',
        'assigned_at',
    ];

    protected $casts = [
        'joined_at'   => 'datetime',
        'assigned_at' => 'datetime',
    ];

    public function session()
    {
        return $this->belongsTo(ChatSession::class, 'session_id');
    }
}