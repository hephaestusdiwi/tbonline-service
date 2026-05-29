<?php

namespace App\Events\Queue;

use App\Models\ChatSession;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CustomerQueued implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public ChatSession $session) {}

    public function broadcastOn(): array
    {
        return [new Channel('queue.admin')];
    }

    public function broadcastAs(): string
    {
        return 'customer.queued';
    }

    public function broadcastWith(): array
    {
        return [
            'session' => [
                'uuid'          => $this->session->uuid,
                'guest_name'    => $this->session->guest_name,
                'status'        => $this->session->status,
                'created_at'    => $this->session->created_at,
                'last_message'  => null,
            ]
        ];
    }
}