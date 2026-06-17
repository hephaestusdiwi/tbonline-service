<?php

namespace App\Events;

use App\Models\ChatSession;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class SessionRated implements ShouldBroadcast
{
    use SerializesModels;

    public function __construct(
        public ChatSession $session,
        public int $rating
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('chat.session.' . $this->session->uuid),
        ];
    }

    public function broadcastAs(): string
    {
        return 'session.rated';
    }

    public function broadcastWith(): array
    {
        return [
            'uuid'   => $this->session->uuid,
            'rating' => $this->rating,
        ];
    }
}