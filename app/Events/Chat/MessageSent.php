<?php

namespace App\Events\Chat;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Message $message) {}

    public function broadcastOn(): array
    {
        // ✅ Load relasi jika belum, ambil UUID dengan aman
        $uuid = $this->message->session?->uuid
            ?? $this->message->load('session')->session?->uuid;

        return [
            new Channel("chat.session.{$uuid}"), // ✅ cocok dengan Vue
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    public function broadcastWith(): array
    {
        return [
            'message' => [
                'id'          => $this->message->id,
                'session_id'  => $this->message->session_id,
                'sender_id'   => $this->message->sender_id,
                'sender_type' => $this->message->sender_type,
                'sender_name' => $this->message->sender?->name ?? $this->getSenderName(),
                'content'     => $this->message->content,
                'type'        => $this->message->type,
                'status'      => $this->message->status,
                'sent_at'     => $this->message->sent_at?->toISOString(),
                'is_read'     => false,
                'attachments' => $this->message->attachments->map(fn($a) => [
                    'id'            => $a->id,
                    'url'           => asset('storage/' . $a->file_path),
                    'original_name' => $a->original_name,
                    'mime_type'     => $a->mime_type,
                ])->toArray(),
            ],
        ];
    }

    private function getSenderName(): string
    {
        return match ($this->message->sender_type) {
            'bot'    => 'Bot',
            'system' => 'System',
            default  => 'Customer',
        };
    }
}