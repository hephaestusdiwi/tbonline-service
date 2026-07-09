<?php

namespace App\Services\Chat;

use App\Models\{Message, User};
use Illuminate\Support\Carbon; 
use Illuminate\Support\Facades\DB;

class ChatReportService
{
    public function staffReport(Carbon $from, Carbon $to): array
    {
        $assignments = DB::table('chat_session_agents')
            ->join('chat_sessions', 'chat_sessions.id', '=', 'chat_session_agents.session_id')
            ->leftJoin('queue_entries', 'queue_entries.session_id', '=', 'chat_sessions.id')
            ->where('chat_session_agents.role', 'primary')
            ->whereBetween('chat_session_agents.assigned_at', [$from, $to])
            ->select([
                'chat_session_agents.agent_id',
                'chat_sessions.id as session_id',
                'chat_sessions.rating',
                'chat_sessions.first_response_at',
                'queue_entries.joined_at',
                'queue_entries.assigned_at as queue_assigned_at',
            ])
            ->get();

        if ($assignments->isEmpty()) {
            return ['rows' => [], 'summary' => $this->emptySummary()];
        }

        $grouped    = $assignments->groupBy('agent_id');
        $agentNames = User::whereIn('id', $grouped->keys())->pluck('name', 'id');

        $rows = [];
        $allTimeToAssign  = [];
        $allFirstResponse = [];
        $allRatings       = [];
        $allSessionIds    = [];

        foreach ($grouped as $agentId => $sessions) {
            $timeToAssign  = [];
            $firstResponse = [];
            $ratings       = [];

            foreach ($sessions as $s) {
                if ($s->joined_at && $s->queue_assigned_at) {
                    $timeToAssign[] = abs(Carbon::parse($s->queue_assigned_at)->diffInSeconds(Carbon::parse($s->joined_at)));
                }
                if ($s->joined_at && $s->first_response_at) {
                    $firstResponse[] = abs(Carbon::parse($s->first_response_at)->diffInSeconds(Carbon::parse($s->joined_at)));
                }
                if ($s->rating) {
                    $ratings[] = $s->rating;
                }
            }

            $sessionIds = $sessions->pluck('session_id')->unique()->values()->all();

            $rows[] = [
                'agent_id'                   => $agentId,
                'agent_name'                 => $agentNames[$agentId] ?? 'Unknown',
                'total_sessions'              => count($sessionIds),
                'avg_time_to_assign_seconds'  => $this->avg($timeToAssign),
                'avg_first_response_seconds'  => $this->avg($firstResponse),
                'avg_response_seconds'        => $this->avgMessageResponse($sessionIds),
                'avg_rating'                  => $ratings ? round(array_sum($ratings) / count($ratings), 2) : null,
            ];

            $allTimeToAssign  = array_merge($allTimeToAssign, $timeToAssign);
            $allFirstResponse = array_merge($allFirstResponse, $firstResponse);
            $allRatings       = array_merge($allRatings, $ratings);
            $allSessionIds    = array_merge($allSessionIds, $sessionIds);
        }

        usort($rows, fn ($a, $b) => $b['total_sessions'] <=> $a['total_sessions']);

        $allSessionIds = array_unique($allSessionIds);

        return [
            'rows'    => $rows,
            'summary' => [
                'total_sessions'              => count($allSessionIds),
                'total_staff'                  => count($rows),
                'avg_time_to_assign_seconds'   => $this->avg($allTimeToAssign),
                'avg_first_response_seconds'   => $this->avg($allFirstResponse),
                'avg_response_seconds'         => $this->avgMessageResponse($allSessionIds),
                'avg_rating'                   => $allRatings ? round(array_sum($allRatings) / count($allRatings), 2) : null,
            ],
        ];
    }

    private function emptySummary(): array
    {
        return [
            'total_sessions' => 0, 'total_staff' => 0,
            'avg_time_to_assign_seconds' => null, 'avg_first_response_seconds' => null,
            'avg_response_seconds' => null, 'avg_rating' => null,
        ];
    }

    private function avg(array $values): ?int
    {
        return count($values) ? (int) round(array_sum($values) / count($values)) : null;
    }

    private function avgMessageResponse(array $sessionIds): ?int
    {
        if (empty($sessionIds)) return null;

        $messages = Message::whereIn('session_id', $sessionIds)
            ->whereIn('sender_type', ['customer', 'agent'])
            ->orderBy('session_id')
            ->orderBy('sent_at')
            ->get(['session_id', 'sender_type', 'sent_at']);

        $diffs   = [];
        $pending = [];

        foreach ($messages as $msg) {
            if ($msg->sender_type === 'customer') {
                $pending[$msg->session_id] = $msg->sent_at;
            } elseif ($msg->sender_type === 'agent' && isset($pending[$msg->session_id])) {
                $diffs[] = abs($msg->sent_at->diffInSeconds($pending[$msg->session_id]));
                unset($pending[$msg->session_id]);
            }
        }

        return $this->avg($diffs);
    }
}