<?php

namespace App\Http\Controllers\Api\V1\Chat;

use App\Http\Controllers\Controller;
use App\Models\ChatSession;
use App\Events\Chat\VisitorLeft;
use App\Services\Chat\MessageService;
use Illuminate\Http\{Request, JsonResponse};

class PresenceController extends Controller
{
    public function ping(Request $request, ChatSession $session): JsonResponse
    {
        $session->update([
            'last_seen_at'  => now(),
            'visitor_left'  => false,
        ]);

        return response()->json([], 204);
    }

    public function leave(Request $request, ChatSession $session): JsonResponse
    {
        \Log::info('leave dipanggil', ['uuid' => $session->uuid, 'status' => $session->status]);

        if (in_array($session->status, ['active', 'queued', 'bot'])) {
            $session->update(['visitor_left' => true]);
            broadcast(new VisitorLeft($session));
            app(MessageService::class)->createSystemMessage(
                $session,
                "{$session->guest_name} telah meninggalkan chat.",
                true 
            );
            \Log::info('leave berhasil', ['uuid' => $session->uuid]);
        } else {
            \Log::info('leave skip - status tidak cocok', ['status' => $session->status]);
        }

        return response()->json([], 204);
    }
}
