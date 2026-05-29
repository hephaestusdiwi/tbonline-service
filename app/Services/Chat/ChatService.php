<?php

namespace App\Services\Chat;

use App\Models\{ChatSession, Message, User};
use App\Services\Queue\QueueService;
use App\Services\Chatbot\ChatbotService;
use App\Events\Chat\MessageSent;
use App\Events\Chat\ChatSessionClosed;
use App\Events\Chat\ChatAssigned;

class ChatService
{
    public function __construct(
        private MessageService $messageService,
        private QueueService   $queueService,
        private ChatbotService $chatbotService,
    ) {}

    public function initiateSession(array $data): ChatSession
    {
        $session = ChatSession::create([
            'customer_id' => null,
            'guest_name'  => $data['guest_name'],
            'guest_phone' => $data['guest_phone'],
            'guest_token' => \Str::uuid(),
            'subject'     => $data['subject'] ?? null,
            'channel'     => $data['channel'] ?? 'web',
            'status'      => 'bot',
            'priority'    => $data['priority'] ?? 'normal',
        ]);

        $this->chatbotService->startSession($session);

        return $session->load('chatbotSession');
    }

    public function sendMessage(ChatSession $session, ?User $sender, array $data): Message
    {
        abort_if($session->isClosed(), 422, 'Session is closed');

        $message = $this->messageService->create($session, $sender, $data);

        if ($session->isBot()) {
            $this->chatbotService->handleInput($session, $message);
            return $message;
        }

        // Record first response time (agent)
        if ($sender && $sender->hasAnyRole(['admin', 'manager', 'staff']) && !$session->first_response_at) {
            $session->update(['first_response_at' => now()]);
        }

        // PENTING: load relasi session & sender sebelum broadcast
        // supaya broadcastOn() bisa akses $message->session->uuid
        $message->load('session', 'sender');

        broadcast(new MessageSent($message))->toOthers();

        return $message;
    }

    public function handoffToQueue(ChatSession $session): void
    {
        $session->update(['status' => 'queued']);

        $this->queueService->enqueue($session);

        // Buat system message — tapi TIDAK broadcast ke channel user
        // karena ini akan tampil via loadMessages() saat pertama kali dibuka
        $this->messageService->createSystemMessage(
            $session,
            'Anda sedang dihubungkan ke customer service kami, Mohon tunggu sebentar ^^'
        );
    }

    public function closeSession(ChatSession $session, string $reason, User $closer): void
    {
        $session->update([
            'status'        => 'closed',
            'closed_reason' => $reason,
            'closed_at'     => now(),
        ]);

        $this->messageService->createSystemMessage($session, 'Sesi chat telah diakhiri');

        broadcast(new ChatSessionClosed($session, $closer));
    }

    public function reopenSession(ChatSession $session): void
    {
        $session->update(['status' => 'queued', 'closed_at' => null]);
    }

    public function assignAgent(ChatSession $session, User $agent): void
    {
        $session->update([
            'assigned_agent_id' => $agent->id,
            'status'            => 'open',
        ]);

        broadcast(new ChatAssigned($session, $agent));
    }
}