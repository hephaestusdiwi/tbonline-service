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

    public function initiateOrderChat(array $data): ChatSession
    {
        $session = ChatSession::where('guest_name', $data['guest_name'])
            ->where('guest_phone', $data['guest_phone'])
            ->whereIn('status', ['bot', 'queued', 'active'])
            ->latest()
            ->first();

        if ($session) {
            if ($session->isBot()) {
                $session->update(['inquiry_type' => 'purchase']);
                $this->handoffToQueue($session);
            }
        } else {
            $session = ChatSession::create([
                'customer_id'  => null,
                'guest_name'   => $data['guest_name'],
                'guest_phone'  => $data['guest_phone'],
                'guest_token'  => \Str::uuid(),
                'subject'      => $data['subject'] ?? 'Pesanan Baru',
                'channel'      => $data['channel'] ?? 'web',
                'status'       => 'queued',
                'priority'     => $data['priority'] ?? 'normal',
                'inquiry_type' => 'purchase',
            ]);

            $this->queueService->enqueue($session);

            $this->messageService->createSystemMessage(
                $session,
                'Pesanan Anda telah diterima. Tim kami akan segera menghubungi Anda di sini untuk konfirmasi.'
            );
        }

        $this->sendMessage($session, null, [
            'content' => $data['order_message'],
            'type'    => 'text',
        ]);

        return $session;
    }

    public function sendMessage(ChatSession $session, ?User $sender, array $data): Message
    {
        abort_if($session->isClosed(), 422, 'Session is closed');

        $message = $this->messageService->create($session, $sender, $data);

        if ($session->isBot()) {
            $this->chatbotService->handleInput($session, $message);
            return $message;
        }

        if ($sender && $sender->hasAnyRole(['admin', 'manager', 'staff']) && !$session->first_response_at) {
            $session->update(['first_response_at' => now()]);
        }

        $message->load('session', 'sender');

        broadcast(new MessageSent($message))->toOthers();

        return $message;
    }

    public function handoffToQueue(ChatSession $session): void
    {
        $session->update(['status' => 'queued']);

        $this->queueService->enqueue($session);

        $this->messageService->createSystemMessage(
            $session,
            'Anda sedang dihubungkan ke customer service kami, mohon tunggu sebentar'
        );
    }

    public function closeSession(ChatSession $session, string $reason, User $closer): void
    {
        $session->update([
            'status'        => 'closed',
            'close_reason'  => $reason,
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