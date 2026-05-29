<?php

namespace App\Events\Chat;

use App\Models\{ChatSession, User};
use Illuminate\Broadcasting\{Channel, InteractsWithSockets};
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class ChatSessionClosed implements ShouldBroadcastNow
{
    use InteractsWithSockets;

    public function __construct(
        public ChatSession $session,
        public ?User $closer
    ) {}

    public function broadcastOn(): array
    {
        return [new Channel("chat.session.{$this->session->uuid}")];
    }

    public function broadcastAs(): string { return 'session.closed'; }

    public function broadcastWith(): array
    {
        return [
            'session_uuid' => $this->session->uuid,
            'closed_by'    => $this->closer?->name ?? 'System',
            'reason'       => $this->session->close_reason,
        ];
    }
}