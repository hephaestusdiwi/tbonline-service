<?php

namespace App\Services\Chatbot;

use App\Models\{ChatSession, ChatbotSession, Message, Order, Complaint};
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
                '5' => ['label' => 'Pembelian / Pesan produk', 'next' => 'collect_purchase'],
            ],
        ],
        'product_inquiry' => [
            'message' => 'Baik, untuk pertanyaan produk silahkan kunjungi FAQ atau ketik pertanyaan kamu',
            'next'    => 'resolve_or_escalate',
        ],
        'order_status' => [
            'message' => 'Mohon masukkan nomor pesanan Kamu (Contoh: INV20250530001):',
            'collect' => 'order_number',
            'next'    => 'show_order_status',
        ],
        'collect_complaint' => [
            'message' => 'Ceritakan masalah kamu :',
            'collect' => 'complaint_text',
            'next'    => 'save_complaint',
        ],
        'collect_purchase' => [
            'message' => 'Boleh sebutkan produk yang ingin Kamu beli? (nama produk, varian/rasa, dan jumlahnya ya)',
            'collect' => 'purchase_text',
            'next'    => 'save_purchase',
        ],
        'resolve_or_escalate' => [
            'message' => 'Apakah masalah kamu sudah terselesaikan?',
            'options' => [
                '1' => ['label' => 'Ya, terima kasih',               'next' => 'resolved'],
                '2' => ['label' => 'Belum, sambungkan dengan CS',     'next' => 'handoff'],
            ],
        ],

        'show_order_status' => ['action' => 'show_order_status'],

        'order_not_found' => [
            'options' => [
                '1' => ['label' => 'Coba nomor lain', 'next' => 'order_status'],
                '2' => ['label' => 'Chat dengan CS',  'next' => 'handoff'],
            ],
        ],

        'save_complaint' => ['action' => 'save_complaint'],
        'save_purchase'  => ['action' => 'save_purchase'],

        'handoff'  => ['action' => 'handoff_to_agent'],
        'resolved' => ['action' => 'mark_resolved'],
    ];

    private array $statusLabels = [
        'pending'    => '🕐 Menunggu Konfirmasi',
        'confirmed'  => '✅ Dikonfirmasi',
        'processing' => '📦 Sedang Diproses',
        'shipped'    => '🚚 Dalam Pengiriman',
        'delivered'  => '🎉 Telah Diterima',
        'cancelled'  => '❌ Dibatalkan',
        'revised'    => '✏️ Dalam Revisi',
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
            $this->handoffWithCategory($session, 'cs');
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
            'handoff_to_agent'  => $this->handoffWithCategory($session, 'cs'),
            'mark_resolved'     => $session->update(['status' => 'resolved', 'resolved_at' => now()]),
            'show_order_status' => $this->handleShowOrderStatus($session, $botSession),
            'save_complaint'    => $this->handleSaveComplaint($session, $botSession),
            'save_purchase'     => $this->handleSavePurchase($session, $botSession),
            default             => null,
        };

        if (!in_array($action, ['show_order_status', 'save_complaint', 'save_purchase'])) {
            $botSession->update(['is_completed' => true, 'handed_off_at' => now()]);
        }
    }

    private function handleShowOrderStatus(ChatSession $session, ChatbotSession $botSession): void
    {
        $invoiceNumber = trim($botSession->context['order_number'] ?? '');

        if (empty($invoiceNumber)) {
            $this->sendBotMessage($session, 'Nomor pesanan tidak ditemukan. Silahkan coba lagi.');
            $botSession->update(['current_node' => 'order_status']);
            return;
        }

        $order = Order::where('invoice_number', strtoupper($invoiceNumber))->first();

        if (!$order) {
            $this->sendBotMessage(
                $session,
                "Pesanan dengan nomor *{$invoiceNumber}* tidak ditemukan.\n" .
                "Pastikan nomor pesanan sudah benar (Contoh: INV20250530001)."
            );

            $this->sendBotMessage($session, 'Apa yang ingin kamu lakukan selanjutnya?');
            $this->sendOptions($session, [
                '1' => ['label' => 'Coba nomor lain',       'next' => 'order_status'],
                '2' => ['label' => 'Chat dengan CS',        'next' => 'handoff'],
            ]);

            $botSession->update(['current_node' => 'order_not_found']);
            return;
        }

        $statusLabel = $this->statusLabels[$order->status] ?? ucfirst($order->status);
        $totalFormatted = 'Rp ' . number_format($order->total_price, 0, ',', '.');

        $message = "📋 *Detail Pesanan*\n" .
                   "━━━━━━━━━━━━━━━━━━\n" .
                   "Invoice     : {$order->invoice_number}\n" .
                   "Nama        : {$order->customer_name}\n" .
                   "Total       : {$totalFormatted}\n" .
                   "📦 Status      : {$statusLabel}\n";

        if ($order->status === 'shipped' && $order->shipping_courier) {
            $message .= "🚚 Kurir       : {$order->shipping_courier} ({$order->shipping_service})\n";
        }

        if ($order->status === 'cancelled' && $order->cancel_reason) {
            $message .= "📝 Alasan      : {$order->cancel_reason}\n";
        }

        $this->sendBotMessage($session, $message);

        $nextConfig = $this->flow['resolve_or_escalate'];
        $botSession->update(['current_node' => 'resolve_or_escalate']);
        $this->sendBotMessage($session, $nextConfig['message']);
        $this->sendOptions($session, $nextConfig['options']);
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

    private function handleSaveComplaint(ChatSession $session, ChatbotSession $botSession): void
    {
        $complaintText = trim($botSession->context['complaint_text'] ?? '');

        if (empty($complaintText)) {
            $this->sendBotMessage($session, 'Maaf, komplain kamu tidak berhasil disimpan. Silahkan coba lagi.');
            $botSession->update(['current_node' => 'collect_complaint']);
            return;
        }

        Complaint::create([
            'session_id'     => $session->id,
            'complaint_text' => $complaintText,
            'customer_name'  => $session->customer_name,
            'customer_phone' => $session->customer_phone,
            'status'         => 'open',
        ]);

        $this->sendBotMessage(
            $session,
            "Komplain kamu sudah kami terima.\n" .
            "Tim CS kami akan segera menghubungi kamu. Mohon tunggu sebentar."
        );

        $this->handoffWithCategory($session, 'complaint');
        $botSession->update(['is_completed' => true, 'handed_off_at' => now()]);
    }

    private function handleSavePurchase(ChatSession $session, ChatbotSession $botSession): void
    {
        $purchaseText = trim($botSession->context['purchase_text'] ?? '');

        if (empty($purchaseText)) {
            $this->sendBotMessage($session, 'Maaf, detail pesanan kamu tidak berhasil disimpan. Silahkan coba lagi.');
            $botSession->update(['current_node' => 'collect_purchase']);
            return;
        }

        // Ringkasan ini dikirim ke thread chat supaya saat di-handoff,
        // agent CS langsung lihat detail pembelian tanpa harus scroll history.
        $this->sendBotMessage(
            $session,
            "📦 *Permintaan Pembelian Baru*\n" .
            "━━━━━━━━━━━━━━━━━━\n" .
            "{$purchaseText}\n" .
            "━━━━━━━━━━━━━━━━━━\n" .
            "Mohon tunggu sebentar, tim CS kami akan segera memproses pesanan kamu."
        );

        $this->handoffWithCategory($session, 'purchase');
        $botSession->update(['is_completed' => true, 'handed_off_at' => now()]);
    }

    /**
     * Tandai kategori sesi (cs / purchase / complaint) sebelum masuk antrian agent,
     * supaya admin bisa filter tab berdasarkan jenis handoff di AdminChat.vue.
     */
    private function handoffWithCategory(ChatSession $session, string $category): void
    {
        $session->update(['inquiry_type' => $category]);
        app(ChatService::class)->handoffToQueue($session);
    }
}