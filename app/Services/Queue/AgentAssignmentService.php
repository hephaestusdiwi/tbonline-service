<?php

namespace App\Services\Queue;

use App\Models\{ChatSession, User};
use App\Events\Chat\ChatAssigned;

class AgentAssignmentService
{
    public function findBestAgent(ChatSession $session): ?User
    {
        return User::role('staff')  // ✅ sesuaikan dengan role name yang kamu pakai
            ->whereHas('onlineStatus', function ($q) {
                $q->where('is_online', true)
                  ->whereColumn('active_chats_count', '<', 'max_chats_capacity');
            })
            ->withCount(['activeSessions'])          // ✅ fix: plural
            ->orderBy('active_sessions_count', 'asc')
            ->first();
    }

    public function assign(ChatSession $session, User $agent): void
    {
        $session->agents()->updateExistingPivot(
            $session->agents()->wherePivot('is_active', true)->pluck('users.id')->toArray(),
            ['is_active' => false, 'left_at' => now()]
        );

        $session->agents()->syncWithoutDetaching([
            $agent->id => [
                'role'          => 'primary',
                'is_active'     => true,
                'assigned_at'   => now(), 
            ],
        ]);

        $session->update(['status' => 'active']);

        $session->queueEntry?->update([
            'status'        => 'assigned',
            'assigned_at'   => now(),
        ]);

        $agent->onlineStatus()->increment('active_chats_count');

        app(\App\Services\Chat\MessageService::class)->createSystemMessage(
            $session,
            "Kamu saat ini terhubung dengan {$agent->name}, Ada yang bisa dibantu?"
        );

        broadcast(new ChatAssigned($session, $agent));
    }

    public function closeSession(ChatSession $session, string $reason, User $closedBy): void
    {
        // Pastikan baris ini ada — nonaktifkan semua agent di pivot
        $session->agents()->updateExistingPivot(
            $session->agents()->wherePivot('is_active', true)->pluck('users.id')->toArray(),
            ['is_active' => false, 'left_at' => now()]
        );

        $session->update([
            'status'    => 'closed',
            'closed_at' => now(),
            'close_reason' => $reason,
        ]);

        // Kurangi active_chats_count agent
        $session->agents->each(function ($agent) {
            $agent->onlineStatus()?->decrement('active_chats_count');
        });
    }
}