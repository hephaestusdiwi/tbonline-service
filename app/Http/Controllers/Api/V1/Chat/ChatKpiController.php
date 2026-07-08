<?php

namespace App\Http\Controllers\Api\V1\Chat;

use App\Http\Controllers\Controller;
use App\Models\ChatSession;
use App\Services\Chat\ChatKpiService;
use Illuminate\Http\JsonResponse;

class ChatKpiController extends Controller
{
    public function __construct(private ChatKpiService $kpiService) {}

    public function show(ChatSession $session): JsonResponse
    {
        return response()->json(['data' => $this->kpiService->metrics($session)]);
    }
}