<?php

namespace App\Services\Chat;

use App\Models\ChatSession;

class ChatKpiService
{
    public function metrics(ChatSession $session): array
    {
        $session->loadMissing('queueEntry');

        $joinedAt   = $session->queueEntry?->joined_at;
        $assignedAt = $session->queueEntry?->assigned_at;

        return [
            'time_to_assign_seconds' => ($joinedAt && $assignedAt)
                ? $assignedAt->diffInSeconds($joinedAt)
                : null,

            'first_response_time_seconds' => ($joinedAt && $session->first_response_at)
                ? $session->first_response_at->diffInSeconds($joinedAt)
                : null,

            'avg_response_seconds' => $this->averageResponseSeconds($session),
        ];
    }

    /**
     * Rata-rata jarak waktu antara pesan customer -> balasan agent berikutnya
     * dalam satu sesi. Kalau ada beberapa pesan agent beruntun, cuma yang
     * pertama setelah pesan customer yang dihitung.
     */
    private function averageResponseSeconds(ChatSession $session): ?float
    {
        $messages = $session->messages()
            ->whereIn('sender_type', ['customer', 'agent'])
            ->orderBy('sent_at')
            ->get(['sender_type', 'sent_at']);

        $diffs = [];
        $pendingCustomerAt = null;

        foreach ($messages as $msg) {
            if ($msg->sender_type === 'customer') {
                $pendingCustomerAt = $msg->sent_at;
            } elseif ($msg->sender_type === 'agent' && $pendingCustomerAt) {
                $diffs[] = $msg->sent_at->diffInSeconds($pendingCustomerAt);
                $pendingCustomerAt = null;
            }
        }

        return count($diffs) ? round(array_sum($diffs) / count($diffs)) : null;
    }
}