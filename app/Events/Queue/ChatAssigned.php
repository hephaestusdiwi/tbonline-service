<?php

namespace App\Events\Queue;

use App\Models\{ChatSession, User};
use Illuminate\Broadcasting\{Channel, InteractsWithSockets};
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class ChatAssigned implements ShouldBroadcastNow
{
    use InteractsWithSockets;

    public function __construct(
        public ChatSession $session,
        public User $agent
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel("session.{$this->session->uuid}"), // ← public channel
        ];
    }

    public function broadcastAs(): string { return 'session.assigned'; } // ← samakan dengan listener

    public function broadcastWith(): array
    {
        return [
            'session_uuid' => $this->session->uuid,
            'agent'        => [
                'id'     => $this->agent->id,
                'name'   => $this->agent->name,
                'avatar' => $this->agent->avatar
                                ? asset('storage/' . $this->agent->avatar)
                                : null,
            ],
        ];
    }
}