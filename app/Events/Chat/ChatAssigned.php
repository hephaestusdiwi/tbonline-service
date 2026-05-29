<?php

namespace App\Events\Chat;

use App\Models\{ChatSession, User};
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatAssigned implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public ChatSession $session,
        public User $agent,
    ) {}

    public function broadcastOn(): array
    {
        return [
            // ✅ Public channel pakai UUID — cocok dengan Vue listener
            new Channel("chat.session.{$this->session->uuid}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'session.assigned';
    }

    public function broadcastWith(): array
    {
        return [
            'session_uuid' => $this->session->uuid,
            'agent' => [
                'id'     => $this->agent->id,
                'name'   => $this->agent->name,
                'avatar' => $this->agent->avatar
                    ? asset('storage/' . $this->agent->avatar)
                    : null,
            ],
            'agent_name' => $this->agent->name,
        ];
    }
}