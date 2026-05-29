<?php

namespace App\Services\Queue;

use App\Models\{ChatSession, QueueEntry};
use App\Events\Queue\CustomerQueued;

class QueueService
{
    public function enqueue(ChatSession $session): QueueEntry
    {
        $position = QueueEntry::where('status', 'waiting')->count() + 1;

        $entry = QueueEntry::create([
            'session_id'             => $session->id,
            'position'               => $position,
            'status'                 => 'waiting',
            'estimated_wait_seconds' => $position * 180,
            'joined_at'              => now(),
        ]);

        // Broadcast posisi antrian ke customer
        broadcast(new \App\Events\Queue\QueuePositionUpdated(
            $session,
            $position,
            $position * 180
        ));

        return $entry;
    }

    public function dequeue(ChatSession $session): void
    {
        $session->queueEntry?->update([
            'status'      => 'assigned',
            'assigned_at' => now(),
        ]);

        // Update posisi antrian yang lain
        $this->recalculatePositions();
    }

    public function recalculatePositions(): void
    {
        QueueEntry::where('status', 'waiting')
            ->orderBy('joined_at')
            ->with('session') // ← load relasi session
            ->each(function ($entry, $index) {
                $newPosition = $index + 1;
                $newWait     = $newPosition * 180;

                $entry->update([
                    'position'               => $newPosition,
                    'estimated_wait_seconds' => $newWait,
                ]);

                // ← broadcast ke masing-masing customer
                broadcast(new \App\Events\Queue\QueuePositionUpdated(
                    $entry->session,
                    $newPosition,
                    $newWait
                ));
            });
    }

    public function cancel(ChatSession $session): void
    {
        $session->queueEntry?->update(['status' => 'cancelled']);
        $this->recalculatePositions();
    }
}