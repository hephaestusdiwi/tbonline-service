<?php

namespace App\Http\Controllers\Api\V1\Chat;

use App\Http\Controllers\Controller;
use App\Models\ChatSession;
use App\Events\Chat\{TypingStarted, TypingStopped};
use Illuminate\Http\{Request, JsonResponse};

class TypingController extends Controller
{
    public function start(Request $request, ChatSession $session): JsonResponse
    {
        $user = $request->user();
        $senderType = $user ? 'agent' : 'customer';

        broadcast(new TypingStarted($session, $user, $senderType))->toOthers();

        return response()->json([], 204);
    }

    public function stop(Request $request, ChatSession $session): JsonResponse
    {
        $user = $request->user();
        $senderType = $user ? 'agent' : 'customer';

        broadcast(new TypingStopped($session, $user, $senderType))->toOthers();

        return response()->json([], 204);
    }
}