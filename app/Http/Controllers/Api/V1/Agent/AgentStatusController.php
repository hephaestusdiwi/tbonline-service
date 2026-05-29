<?php

namespace App\Http\Controllers\Api\V1\Agent;

use App\Http\Controllers\Controller;
use App\Models\UserOnlineStatus;
use Illuminate\Http\{Request, JsonResponse};

class AgentStatusController extends Controller
{
    public function goOnline(Request $request): JsonResponse
    {
        UserOnlineStatus::updateOrCreate(
            ['user_id' => $request->user()->id],
            [
                'is_online'    => true,
                'last_ping_at' => now(),
            ]
        );

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

        return response()->json(['message' => 'Status updated to offline.']);
    }
}