<?php

namespace App\Http\Controllers\Api\V1\Agent;

use App\Http\Controllers\Controller;
use App\Models\UserOnlineStatus;
use App\Events\AgentStatusChanged;
use Illuminate\Http\{Request, JsonResponse};

class AgentStatusController extends Controller
{
    // Anggap offline kalau ping terakhir > 2 menit yang lalu
    private const ONLINE_TIMEOUT_MINUTES = 2;

    public function goOnline(Request $request): JsonResponse
    {
        UserOnlineStatus::updateOrCreate(
            ['user_id' => $request->user()->id],
            [
                'is_online'    => true,
                'last_ping_at' => now(),
            ]
        );

        $onlineCount = $this->countOnline();

        broadcast(new AgentStatusChanged(
            anyOnline: $onlineCount > 0,
            onlineCount: $onlineCount
        ));

        return response()->json(['message' => 'Status updated to online.']);
    }

    public function goOffline(Request $request): JsonResponse
    {
        UserOnlineStatus::updateOrCreate(
            ['user_id' => $request->user()->id],
            [
                'is_online'    => false,
                'last_ping_at' => now(),
            ]
        );

        $onlineCount = $this->countOnline();

        broadcast(new AgentStatusChanged(
            anyOnline: $onlineCount > 0,
            onlineCount: $onlineCount
        ));

        return response()->json(['message' => 'Status updated to offline.']);
    }

    private function countOnline(): int
    {
        return UserOnlineStatus::where('is_online', true)
            ->where('last_ping_at', '>=', now()->subMinutes(self::ONLINE_TIMEOUT_MINUTES))
            ->count();
    }
}