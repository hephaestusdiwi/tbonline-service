<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChatSession extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'uuid', 'customer_id', 'subject', 'channel', 'status',
        'priority', 'rating', 'close_reason', 'tags', 'metadata',
        'first_response_at', 'resolved_at', 'closed_at',
        'guest_name', 'guest_phone', 'guest_token',
        'last_seen_at', 'visitor_left', 'inquiry_type',
    ];

    protected $casts = [
        'tags'              => 'array',
        'metadata'          => 'array',
        'first_response_at' => 'datetime',
        'resolved_at'       => 'datetime',
        'closed_at'         => 'datetime',
        'last_seen_at'      => 'datetime',
        'visitor_left'      => 'boolean',  
    ];

    public function scopeWithLastMessage($query)
    {
        return $query->addSelect([
            'last_message_content' => \App\Models\Message::select('content')
                ->whereColumn('session_id', 'chat_sessions.id')
                ->latest('sent_at')
                ->limit(1),
            'last_message_at' => \App\Models\Message::select('sent_at')
                ->whereColumn('session_id', 'chat_sessions.id')
                ->latest('sent_at')
                ->limit(1),
        ]);
    }

    protected static function booted(): void
    {
        static::creating(fn ($model) => $model->uuid ??= Str::uuid());
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function agents()
    {
        return $this->belongsToMany(User::class, 'chat_session_agents', 'session_id', 'agent_id')->withPivot('role', 'is_active', 'assigned_at');
    }

    public function primaryAgent()
    {
        return $this->agents()->wherePivot('role', 'primary')->wherePivot('is_active', true)->first();
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'session_id')->orderBy('sent_at');
    }

    public function queueEntry()
    {
        return $this->hasOne(QueueEntry::class, 'session_id');
    }

    public function chatbotSession()
    {
        return $this->hasOne(ChatbotSession::class, 'session_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isQueued(): bool
    {
        return $this->status === 'queued';
    }

    public function isBot(): bool
    {
        return $this->status === 'bot';
    }

    public function isClosed(): bool
    {
        return in_array($this->status, ['closed', 'resolved']);
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}