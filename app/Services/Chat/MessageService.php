<?php

namespace App\Services\Chat;

use App\Models\{ChatSession, Message, User};
use App\Events\Chat\MessageSent; // ✅ tambah import

class MessageService
{
    public function create(ChatSession $session, ?User $sender, array $data): Message
    {
        $message = Message::create([
            'session_id'  => $session->id,
            'sender_id'   => $sender?->id,
            'sender_type' => $sender ? $this->resolveSenderType($sender) : 'customer',
            'content'     => $data['content'] ?? null,
            'type'        => $data['type'] ?? 'text',
            'status'      => 'sent',
            'is_bot'      => false,
            'sent_at'     => now(),
        ]);

        // ✅ Load relasi session (untuk dapat UUID di broadcastOn)
        $message->load('session', 'sender', 'attachments');

        // ✅ Broadcast ke visitor & agent
        broadcast(new MessageSent($message));

        return $message;
    }

    public function createSystemMessage(ChatSession $session, string $content, bool $internal = false): Message
    {
        $message = Message::create([
            'session_id'  => $session->id,
            'sender_id'   => null,
            'sender_type' => 'system',
            'content'     => $content,
            'type'        => $internal ? 'system_internal' : 'system_event',
            'status'      => 'sent',
            'is_bot'      => false,
            'sent_at'     => now(),
        ]);

        $message->load('session');
        broadcast(new MessageSent($message));

        return $message;
    }

    private function resolveSenderType(User $user): string
    {
        if ($user->hasAnyRole(['admin', 'manager', 'staff'])) return 'agent';
        return 'customer';
    }
}