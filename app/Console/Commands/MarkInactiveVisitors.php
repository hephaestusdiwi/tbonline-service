<?php

namespace App\Console\Commands;

use App\Models\ChatSession;
use App\Events\Chat\VisitorLeft;
use App\Services\Chat\MessageService;
use Illuminate\Console\Command;

class MarkInactiveVisitors extends Command
{
    protected $signature   = 'chat:mark-inactive-visitors';
    protected $description = 'Mark visitors as left if no ping for 2 minutes';

    public function handle(MessageService $messageService): void
    {
        $sessions = ChatSession::whereIn('status', ['active', 'queued', 'bot'])
            ->where('visitor_left', false)
            ->where('last_seen_at', '<=', now()->subMinutes(2))
            ->get();

        foreach ($sessions as $session) {
            $session->update(['visitor_left' => true]);
            broadcast(new VisitorLeft($session));
            $messageService->createSystemMessage(
                $session,
                "{$session->guest_name} telah meninggalkan chat.",
                true
            );
        }
    }
}