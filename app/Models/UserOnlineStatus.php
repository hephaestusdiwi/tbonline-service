<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserOnlineStatus extends Model
{
    protected $fillable = [
        'user_id',
        'is_online',
        'socket_id',
        'active_chats_count',
        'max_chats_capacity',
        'last_ping_at',
    ];

    protected $casts = [
        'is_online' => 'boolean',
        'last_ping_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}