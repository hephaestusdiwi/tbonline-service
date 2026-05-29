<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotSession extends Model
{
    protected $fillable = [
        'session_id',
        'current_node',
        'context',
        'is_completed',
        'needs_agent',
        'handed_off_at',
    ];

    protected $casts = [
        'context'      => 'array',
        'is_completed' => 'boolean',
        'needs_agent'  => 'boolean',
        'handed_off_at'=> 'datetime',
    ];

    public function session()
    {
        return $this->belongsTo(ChatSession::class, 'session_id');
    }
}