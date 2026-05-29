<?php

namespace App\Events\Chat;

use App\Models\{ChatSession, User};
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TypingStopped implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public ChatSession $session,
        public ?User $user,
        public string $senderType,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel("session.{$this->session->uuid}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'typing.stopped';
    }

    public function broadcastWith(): array
    {
        return [
            'sender_type' => $this->senderType,
            'sender_name' => $this->user?->name ?? 'Customer',
        ];
    }
}