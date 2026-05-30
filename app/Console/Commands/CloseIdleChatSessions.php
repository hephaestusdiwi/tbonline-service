<?php
namespace App\Console\Commands;

use App\Models\ChatSession;
use App\Services\Chat\MessageService;
use Illuminate\Console\Command;

class CloseIdleChatSessions extends Command
{
    protected $signature   = 'chat:close-idle';
    protected $description = 'Auto-close sesi chat yang idle lebih dari 5 menit';

    public function handle(MessageService $messageService): void
    {
        $idleThreshold = now()->subMinutes(5);

        $sessions = ChatSession::whereIn('status', ['active', 'queued', 'bot'])
            ->where('last_seen_at', '<', $idleThreshold)
            ->whereNotNull('last_seen_at')
            ->get();

        $this->info('Sesi idle ditemukan: ' . $sessions->count());
        \Log::info('chat:close-idle', [
            'count'     => $sessions->count(),
            'threshold' => $idleThreshold,
        ]);

        $sessions->each(function ($session) use ($messageService) {
            \Log::info('closing session', [
                'uuid'         => $session->uuid,
                'last_seen_at' => $session->last_seen_at,
            ]);

            try {
                $session->update(['visitor_left' => true]);
                \Log::info('visitor_left updated', ['uuid' => $session->uuid]);

                broadcast(new \App\Events\Chat\VisitorLeft($session));
                \Log::info('VisitorLeft broadcasted', ['uuid' => $session->uuid]);

                $messageService->createSystemMessage(
                    $session,
                    "{$session->guest_name} telah meninggalkan chat.",
                    true
                );

                $messageService->createSystemMessage(
                    $session,
                    "Sesi ditutup otomatis karena visitor tidak aktif.",
                    false
                );

                $session->update([
                    'status'       => 'closed',
                    'closed_at'    => now(),
                    'close_reason' => 'idle_timeout',
                ]);

                broadcast(new \App\Events\Chat\ChatSessionClosed($session, null));

                \Log::info('session closed', ['uuid' => $session->uuid]);

            } catch (\Exception $e) {
                \Log::error('gagal close session', [
                    'uuid'  => $session->uuid,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        });
    }
}