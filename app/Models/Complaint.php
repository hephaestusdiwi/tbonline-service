<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    protected $fillable = [
        'session_id',
        'complaint_text',
        'customer_name',
        'customer_phone',
        'status',
        'resolved_at',
        'resolved_by',
        'resolution_note',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function session()
    {
        return $this->belongsTo(ChatSession::class);
    }

    public function resolver()
    {
        return $this->belongsTo(\App\Models\User::class, 'resolved_by');
    }
}