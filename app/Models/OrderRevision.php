<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderRevision extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'order_id', 'revised_by',
        'before', 'after', 'changes_summary',
        'note', 'created_at',
    ];

    protected $casts = [
        'before'          => 'array',
        'after'           => 'array',
        'changes_summary' => 'array',
        'created_at'      => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function revisor()
    {
        return $this->belongsTo(\App\Models\User::class, 'revised_by');
    }
}