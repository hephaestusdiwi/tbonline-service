<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\{User, QueueEntry, ChatSession};
use Illuminate\Http\{Request, JsonResponse};

class QueueController extends Controller
{
    public function index(): JsonResponse
    {
        $queue = QueueEntry::with(['session.customer'])
            ->where('status', 'waiting')
            ->orderBy('joined_at')
            ->get();

        return response()->json(['data' => $queue]);
    }

    public function stats(): JsonResponse
    {
        return response()->json([
            'waiting'  => QueueEntry::where('status', 'waiting')->count(),
            'active'   => ChatSession::where('status', 'active')->count(),
            'today'    => ChatSession::whereDate('created_at', today())->count(),
        ]);
    }

    public function agents(): JsonResponse
    {
        $agents = User::role(['admin', 'manager', 'staff'])
            ->with('onlineStatus')
            ->get()
            ->map(fn($u) => [
                'id'        => $u->id,
                'name'      => $u->name,
                'is_online' => $u->onlineStatus?->is_online ?? false,
                'active_chats' => $u->onlineStatus?->active_chats_count ?? 0,
            ]);

        return response()->json(['data' => $agents]);
    }

    public function manualAssign(Request $request, QueueEntry $entry): JsonResponse
    {
        $data  = $request->validate(['agent_id' => 'required|exists:users,id']);
        $agent = User::findOrFail($data['agent_id']);

        app(\App\Services\Queue\AgentAssignmentService::class)
            ->assign($entry->session, $agent);

        return response()->json(['message' => 'Assigned.']);
    }
}