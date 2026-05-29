<?php

namespace App\Services\Chatbot;

use App\Models\{ChatSession, ChatbotSession, Message};
use App\Services\Chat\ChatService;

class ChatbotService
{
    private array $flow = [
        'greeting' => [
            'message' => 'Halo, Selamat datang di Two Brothers Vape Store. Ada yang bisa kami bantu?',
            'options' => [
                '1' => ['label' => 'Pertanyaan produk',     'next' => 'product_inquiry'],
                '2' => ['label' => 'Status pesanan',        'next' => 'order_status'],
                '3' => ['label' => 'Komplain',              'next' => 'collect_complaint'],
                '4' => ['label' => 'Chat dengan CS',        'next' => 'handoff'],
            ],
        ],
        'product_inquiry' => [
            'message' => 'Baik, untuk pertanyaan produk silahkan kunjungi FAQ atau ketik pertanyaan kamu',
            'next'    => 'resolve_or_escalate',
        ],
        'order_status' => [
            'message' => 'Mohon masukkan nomor pesanan Kamu (Contoh: INV-123654):',
            'collect' => 'order_number',
            'next'    => 'show_order_status',
        ],
        'collect_complaint' => [
            'message' => 'Ceritakan masalah kamu :',
            'collect' => 'complaint_text',
            'next'    => 'handoff',
        ],
        'resolve_or_escalate' => [
            'message' => 'Apakah masalah kamu sudah terselesaikan?',
            'options' => [
                '1' => ['label' => 'Ya, terima kasih',               'next' => 'resolved'],
                '2' => ['label' => 'Belum, sambungkan dengan CS',     'next' => 'handoff'],
            ],
        ],
        'handoff'  => ['action' => 'handoff_to_agent'],
        'resolved' => ['action' => 'mark_resolved'],
    ];

    public function startSession(ChatSession $session): void
    {
        $botSession = ChatbotSession::firstOrCreate(
            ['session_id' => $session->id],
            [
                'current_node' => 'greeting',
                'context'      => [],
            ]
        );

        // Hanya kirim greeting kalau session baru dibuat
        if ($botSession->wasRecentlyCreated) {
            $this->sendBotMessage($session, $this->flow['greeting']['message']);
            $this->sendOptions($session, $this->flow['greeting']['options'] ?? []);
        }
    }

    public function handleInput(ChatSession $session, Message $userMessage): void
    {
        $botSession = $session->chatbotSession;
        $node       = $this->flow[$botSession->current_node] ?? null;

        if (!$node) {
            $this->sendBotMessage($session, 'Maaf, terjadi kesalahan. Menghubungkan ke CS...');
            app(ChatService::class)->handoffToQueue($session);
            return;
        }

        if (isset($node['collect'])) {
            $context = $botSession->context ?? [];
            $context[$node['collect']] = $userMessage->content;
            $botSession->update(['context' => $context]);
        }

        $nextNode = $this->resolveNextNode($node, $userMessage->content);

        if (!$nextNode) {
            $this->sendBotMessage($session, 'Pilihan tidak valid, silahkan coba lagi');
            return;
        }

        $nextConfig = $this->flow[$nextNode];

        if (isset($nextConfig['action'])) {
            $this->handleAction($nextConfig['action'], $session, $botSession);
            return;
        }

        $botSession->update(['current_node' => $nextNode]);

        if (isset($nextConfig['message'])) {
            $this->sendBotMessage($session, $nextConfig['message']);
        }
        if (isset($nextConfig['options'])) {
            $this->sendOptions($session, $nextConfig['options']);
        }
    }

    private function resolveNextNode(array $node, string $input): ?string
    {
        if (isset($node['options'])) {
            return $node['options'][trim($input)]['next'] ?? null;
        }
        return $node['next'] ?? null;
    }

    private function handleAction(string $action, ChatSession $session, ChatbotSession $botSession): void
    {
        match ($action) {
            'handoff_to_agent' => app(ChatService::class)->handoffToQueue($session),
            'mark_resolved'    => $session->update(['status' => 'resolved', 'resolved_at' => now()]),
            default            => null,
        };

        $botSession->update(['is_completed' => true, 'handed_off_at' => now()]);
    }

    private function sendBotMessage(ChatSession $session, string $content): void
    {
        $message = Message::create([
            'session_id'  => $session->id,
            'sender_id'   => null,
            'sender_type' => 'bot',
            'content'     => $content,
            'type'        => 'text',
            'is_bot'      => true,
            'sent_at'     => now(),
        ]);

        // PENTING: load relasi session supaya broadcastOn() dapat UUID
        $message->setRelation('session', $session);

        broadcast(new \App\Events\Chat\MessageSent($message));
    }

    private function sendOptions(ChatSession $session, array $options): void
    {
        if (empty($options)) return;

        $optionText = collect($options)
            ->map(fn($opt, $key) => "{$key}. {$opt['label']}")
            ->implode("\n");

        $this->sendBotMessage($session, $optionText);
    }
}