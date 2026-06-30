<?php

namespace App\Http\Controllers\Api\V1\Chat;

use App\Http\Controllers\Controller;
use App\Services\Chat\ChatService;
use App\Models\ChatSession;
use App\Http\Resources\SessionResource;
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Support\Str;
use App\Services\Queue\AgentAssignmentService;

class SessionController extends Controller
{
    public function __construct(private ChatService $chatService) {}

    // Guest mulai sesi — tidak perlu login
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'guest_name'  => 'required|string|max:100',
            'guest_phone' => 'required|string|max:20',
            'subject'     => 'nullable|string|max:255',
            'channel'     => 'nullable|in:web,mobile',
        ]);

        // ✅ Cari session aktif berdasarkan nama + nomor HP
        $existing = ChatSession::where('guest_name', $data['guest_name'])
            ->where('guest_phone', $data['guest_phone'])
            ->whereIn('status', ['bot', 'queued', 'active'])
            ->latest()
            ->first();

        if ($existing) {
            return response()->json([
                'data'        => new SessionResource($existing->load(['agents', 'queueEntry'])),
                'guest_token' => $existing->guest_token,
            ]);
        }

        // Tidak ada session aktif → buat baru
        $session = $this->chatService->initiateSession($data);

        return response()->json([
            'data'        => new SessionResource($session),
            'guest_token' => $session->guest_token,
        ], 201);
    }

    public function storeFromOrder(Request $request): JsonResponse
    {
        $data = $request->validate([
            'guest_name'    => 'required|string|max:100',
            'guest_phone'   => 'required|string|max:20',
            'order_message' => 'required|string|max:3000',
            'subject'       => 'nullable|string|max:255',
        ]);

        $session = $this->chatService->initiateOrderChat($data);

        return response()->json([
            'data'          => new SessionResource($session->load(['agents', 'queueEntry'])),
            'guest_token'   => $session->guest_token,
        ], 201);
    }

    // Guest ambil sesi by token
    public function showByToken(Request $request): JsonResponse
    {
        $session = ChatSession::where('guest_token', $request->guest_token)
            ->firstOrFail();

        return response()->json([
            'data' => new SessionResource($session->load(['agents', 'queueEntry']))
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->hasAnyRole(['admin', 'manager'])) {
            $sessions = ChatSession::with(['agents', 'queueEntry'])
                ->withLastMessage() // ← ganti dari with('messages')
                ->latest()
                ->paginate(20);
        } else {
            $sessions = ChatSession::with(['agents', 'queueEntry'])
                ->withLastMessage() // ← tambah di sini juga
                ->where(function ($q) use ($user) {
                    $q->where('status', 'queued')
                    ->orWhere(function ($q2) use ($user) {
                        $q2->where('status', 'active')
                            ->whereHas('agents', fn ($q3) =>
                                $q3->where('chat_session_agents.agent_id', $user->id)
                                    ->where('chat_session_agents.is_active', true)
                            );
                    })
                    ->orWhere('status', 'active')
                        ->orWhere(function ($q2) use ($user) {
                            $q2->whereIn('status', ['closed', 'resolved'])
                                ->whereHas('agents', fn ($q3) =>
                                    $q3->where('chat_session_agents.agent_id', $user->id)
                                );
                        });
                })
                ->latest()
                ->paginate(20);
        }

        $sessions->getCollection()->transform(function ($session) use ($user) {
            $ismine = $session->agents->contains(fn ($a) => $a->id === $user->id && (bool) $a->pivot->is_active);
            $session->is_mine   = $ismine;
            $session->can_reply = $user->hasAnyRole(['admin', 'manager']) || $ismine;
            return $session;
        });

        return response()->json(SessionResource::collection($sessions)->resource);
    }

    public function close(Request $request, ChatSession $session): JsonResponse
    {
        $data = $request->validate(['reason' => 'required|string|max:500']);
        $this->chatService->closeSession($session, $data['reason'], $request->user());

        return response()->json(['message' => 'Session closed']);
    }

    public function reopen(ChatSession $session): JsonResponse
    {
        $this->chatService->reopenSession($session);
        return response()->json(['message' => 'Session reopened']);
    }

    public function assign(Request $request, ChatSession $session): JsonResponse
    {
        $data  = $request->validate(['agent_id' => 'required|exists:users,id']);
        $agent = \App\Models\User::findOrFail($data['agent_id']);

        app(AgentAssignmentService::class)->assign($session, $agent);

        // Update assigned_agent_name di response supaya Vue bisa langsung update sidebar
        return response()->json([
            'message'    => 'Agent assigned.',
            'agent_name' => $agent->name,
        ]);
    }

    public function take(Request $request, ChatSession $session): JsonResponse
    {
        $user = $request->user();

        if ($session->status !== 'queued') {
            return response()->json(['message' => 'Session sudah diambil atau tidak tersedia'], 422);
        }

        $alreadyAssigned = $session->agents()
            ->wherePivot('role', 'primary')
            ->wherePivot('is_active', true)
            ->where('users.id', '!=', $user->id) 
            ->exists();

        if ($alreadyAssigned) {
            return response()->json(['message' => 'Session sudah diambil agent lain'], 409);
        }

        $isAlreadyMine = $session->agents()
        ->wherePivot('role', 'primary')
        ->wherePivot('is_active', true)
        ->where('users.id', $user->id)
        ->exists();

        if ($isAlreadyMine) {
            return response()->json(['message' => 'Session sudah Anda ambil.', 'agent_name' => $user->name]);
        }

        if ($session->status !== 'queued') {
            return response()->json(['message' => 'Session tidak tersedia.'], 422);
        }

        app(AgentAssignmentService::class)->assign($session, $user);

        return response()->json([
            'message'    => 'Session berhasil diambil.',
            'agent_name' => $user->name,
        ]);
    }

    public function destroy(Request $request, ChatSession $session): JsonResponse
    {
        // Hanya admin yang boleh hapus
        if (!$request->user()->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $session->delete();

        return response()->json(['message' => 'Sesi berhasil dihapus']);
    }

    public function rate(Request $request, ChatSession $session): JsonResponse
    {
        $data = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
        ]);

        if (!$session->isClosed()) {
            return response()->json(['message' => 'Sesi belum ditutup'], 422);
        }

        if ($session->rating) {
            return response()->json(['message' => 'Sesi sudah dirating'], 422);
        }

        $session->update(['rating' => $data['rating']]);

        broadcast(new \App\Events\SessionRated($session, $data['rating']))->toOthers();

        return response()->json(['message' => 'Rating berhasil disimpan']);
    }
}