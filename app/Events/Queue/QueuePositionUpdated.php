<?php

namespace App\Events\Queue;

use App\Models\{ChatSession, QueueEntry};
use Illuminate\Broadcasting\{Channel, InteractsWithSockets};
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class QueuePositionUpdated implements ShouldBroadcastNow
{
    use InteractsWithSockets;

    public function __construct(
        public ChatSession $session,
        public int $position,
        public int $estimatedWait
    ) {}

    public function broadcastOn(): array
    {
        return [new Channel("session.{$this->session->uuid}")];
    }

    public function broadcastAs(): string { return 'queue.position'; }

    public function broadcastWith(): array
    {
        return [
            'position'       => $this->position,
            'estimated_wait' => $this->estimatedWait,
        ];
    }
}