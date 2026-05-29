<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'session_id', 'sender_id', 'sender_type', 'content',
        'type', 'status', 'is_bot', 'metadata', 'sent_at',
    ];

    protected $casts = [
        'is_bot'   => 'boolean',
        'metadata' => 'array',
        'sent_at'  => 'datetime',
    ];

    public function session()
    {
        return $this->belongsTo(ChatSession::class, 'session_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function reads()
    {
        return $this->hasMany(MessageRead::class);
    }

    public function attachments()
    {
        return $this->hasMany(MessageAttachment::class);
    }

    public function markReadBy(int $userId): void
    {
        $this->reads()->firstOrCreate(
            ['user_id' => $userId],
            ['read_at' => now()]
        );
        $this->update(['status' => 'read']);
    }
}