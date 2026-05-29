<?php

namespace App\Events\Chat;

use App\Models\ChatSession;
use Illuminate\Broadcasting\{Channel, InteractsWithSockets};
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class VisitorLeft implements ShouldBroadcastNow
{
    use InteractsWithSockets;

    public function __construct(public ChatSession $session) {}

    public function broadcastOn(): array
    {
        return [
            new Channel("chat.admin"),
            new Channel("chat.session.{$this->session->uuid}"),
        ];
    }

    public function broadcastAs(): string { return 'visitor.left'; }

    public function broadcastWith(): array
    {
        return [
            'session_uuid' => $this->session->uuid,
            'guest_name'   => $this->session->guest_name,
        ];
    }
}