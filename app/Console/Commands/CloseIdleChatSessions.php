<?php
namespace App\Console\Commands;

use App\Models\ChatSession;
use App\Services\Chat\MessageService;
use App\Services\Queue\QueueService;
use Illuminate\Console\Command;

class CloseIdleChatSessions extends Command
{
    protected $signature   = 'chat:close-idle';
    protected $description = 'Auto-close sesi chat yang idle lebih dari 1 jam';

    public function handle(MessageService $messageService, QueueService $queueService): void
    {
        $idleThreshold = now()->subHour();

        $sessions = ChatSession::whereIn('status', ['active', 'queued', 'bot'])
            ->where('last_seen_at', '<', $idleThreshold)
            ->whereNotNull('last_seen_at')
            ->get();

        $this->info('Sesi idle ditemukan: ' . $sessions->count());
        \Log::info('chat:close-idle', ['count' => $sessions->count(), 'threshold' => $idleThreshold]);

        $sessions->each(function ($session) use ($messageService, $queueService) {
            try {
                if ($session->status === 'queued') {
                    $queueService->cancel($session);   // ← baris baru, bersihin queue entry-nya
                }

                $session->update(['visitor_left' => true]);
                broadcast(new \App\Events\Chat\VisitorLeft($session));

                $messageService->createSystemMessage($session, "{$session->guest_name} telah meninggalkan chat.", true);
                $messageService->createSystemMessage($session, "Sesi ditutup otomatis karena visitor tidak aktif.", false);

                $session->update([
                    'status'       => 'closed',
                    'closed_at'    => now(),
                    'close_reason' => 'idle_timeout',
                ]);

                broadcast(new \App\Events\Chat\ChatSessionClosed($session, null));
            } catch (\Exception $e) {
                \Log::error('gagal close session', ['uuid' => $session->uuid, 'error' => $e->getMessage()]);
            }
        });
    }
}