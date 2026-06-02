<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'actor_id',
        'action',
        'description',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function user()  { return $this->belongsTo(User::class); }
    public function actor() { return $this->belongsTo(User::class, 'actor_id'); }

    public static function record(User $user, string $action, string $description): void
    {
        static::create([
            'user_id'     => $user->id,
            'actor_id'    => Auth::id(),
            'action'      => $action,
            'description' => $description,
            'created_at'  => now(),
        ]);
    }
}
