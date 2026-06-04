<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AgentStatusChanged implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public bool $anyOnline,
        public int  $onlineCount
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel('agents.status');
    }

    public function broadcastAs(): string
    {
        return 'status.changed';
    }

    public function broadcastWith(): array
    {
        return [
            'any_online'   => $this->anyOnline,
            'online_count' => $this->onlineCount,
        ];
    }
}