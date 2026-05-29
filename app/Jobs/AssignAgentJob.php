<?php

namespace App\Jobs;

use App\Models\ChatSession;
use App\Services\Queue\AgentAssignmentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable; // ← tambah ini
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};

class AssignAgentJob implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels; // ← tambah Dispatchable

    public int $tries  = 3;  // ← fix typo 'thies' → 'tries'
    public int $backoff = 10;

    public function __construct(private ChatSession $session) {}

    public function handle(AgentAssignmentService $service): void
    {
        $this->session->refresh();

        if (!$this->session->isQueued()) return;

        $agent = $service->findBestAgent($this->session);

        if (!$agent) {
            $this->session->queueEntry?->update([
                'estimated_wait_seconds' => $this->estimateWait(),
            ]);

            broadcast(new \App\Events\Queue\QueuePositionUpdated(
                $this->session,
                $this->session->queueEntry->position ?? 0
            ));

            self::dispatch($this->session)
                ->delay(now()->addSeconds(30))
                ->onQueue('assignments');

            return;
        }

        $service->assign($this->session, $agent);
    }

    private function estimateWait(): int
    {
        return ($this->session->queueEntry?->position ?? 1) * 180;
    }
}