<?php

namespace App\Http\Controllers\Api\V1\Chat;

use App\Http\Controllers\Controller;
use App\Services\Chat\ChatService;
use App\Models\{ChatSession, Message};
use App\Http\Resources\MessageResource;
use Illuminate\Http\{Request, JsonResponse};

class MessageController extends Controller
{
    public function __construct(private ChatService $chatService) {}

    public function index(Request $request, ChatSession $session): JsonResponse
    {
        $messages = $session->messages()
            ->with(['sender', 'attachments'])
            ->latest('sent_at')
            ->paginate(50);

        return response()->json(MessageResource::collection($messages)->resource);
    }

    public function store(Request $request, ChatSession $session): JsonResponse
    {
        $data = $request->validate([
            'content' => 'required|string|max:5000',
            'type'    => 'nullable|in:text,image,file,audio',
        ]);

        $sender = auth('sanctum')->user();

        if ($sender && $sender->hasRole('staff')) {
            $isAssignee = $session->agents()
                ->wherePivot('agent_id', $sender->id)
                ->wherePivot('is_active', true)
                ->exists();

            if (!$isAssignee) {
                return response()->json(['message' => 'Anda hanya bisa membaca session ini'], 403);
            }
        }        

        $message = $this->chatService->sendMessage($session, $sender, $data);

        return response()->json(['data' => new MessageResource($message->load('sender', 'attachments'))], 201);
    }

    public function markRead(Request $request, ChatSession $session): JsonResponse
    {
        // hanya jalankan kalau user login (agent)
        if ($request->user()) {
            $session->messages()
                ->where('sender_id', '!=', $request->user()->id)
                ->whereDoesntHave('reads', fn ($q) => $q->where('user_id', $request->user()->id))
                ->each(fn ($msg) => $msg->markReadBy($request->user()->id));
        }

        return response()->json(['message' => 'Messages marked as read.']);
    }
}