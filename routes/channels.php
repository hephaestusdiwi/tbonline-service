<?php

use App\Models\ChatSession;
use App\Models\ChatSessionAgent;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Semua channel Pusher didaftarkan di sini.
|
| Aturan akses:
|   - admin/manager  → bisa masuk ke semua channel chat
|   - staff          → hanya channel yang berkaitan dengan sesi mereka
|   - customer       → hanya channel sesi milik mereka sendiri
|
*/

// ──────────────────────────────────────────────────────────────────────────
// 1. PRIVATE CHANNEL — satu sesi chat
//    Dipakai untuk: MessageSent, TypingStarted, TypingStopped,
//                   ChatSessionClosed, ChatSessionReopened
//
//    Format: private-chat.session.{sessionId}
// ──────────────────────────────────────────────────────────────────────────
Broadcast::channel('chat.session.{sessionId}', function ($user, $sessionId) {
    // Admin & manager selalu boleh masuk
    if ($user->hasAnyRole(['admin', 'manager'])) {
        return true;
    }

    $session = ChatSession::find($sessionId);

    if (! $session) {
        return false;
    }

    // Staff boleh masuk jika mereka adalah agent yang assigned ke sesi ini
    if ($user->hasRole('staff')) {
        return ChatSessionAgent::where('chat_session_id', $sessionId)
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->exists();
    }

    // Customer boleh masuk ke sesi milik mereka sendiri
    // (gunakan guard customer jika kamu punya guard terpisah,
    //  atau cek kolom customer_identifier di tabel chat_sessions)
    if (isset($session->customer_id) && $session->customer_id === $user->id) {
        return true;
    }

    return false;
});

// ──────────────────────────────────────────────────────────────────────────
// 2. PRESENCE CHANNEL — semua agent aktif
//    Dipakai untuk: status online agent, daftar agent yang sedang aktif
//
//    Format: presence-chat.agents
// ──────────────────────────────────────────────────────────────────────────
Broadcast::channel('chat.agents', function ($user) {
    if (! $user->hasAnyPermission(['chat_view', 'chat_manage', 'chat_admin'])) {
        return false;
    }

    // Data yang dikirim ke client presence channel
    return [
        'id'     => $user->id,
        'name'   => $user->name,
        'email'  => $user->email,
        'avatar' => $user->avatar ?? null,
        'role'   => $user->getRoleNames()->first(),
    ];
});

// ──────────────────────────────────────────────────────────────────────────
// 3. PRIVATE CHANNEL — notifikasi per-agent
//    Dipakai untuk: NotifyAgentNewChatJob, ChatAssigned event
//
//    Format: private-chat.agent.{userId}
// ──────────────────────────────────────────────────────────────────────────
Broadcast::channel('chat.agent.{userId}', function ($user, $userId) {
    // Agent hanya boleh subscribe ke channel notifikasi milik mereka sendiri
    // Admin/manager boleh subscribe ke channel agent manapun (untuk monitoring)
    if ($user->hasAnyRole(['admin', 'manager'])) {
        return true;
    }

    return (int) $user->id === (int) $userId
        && $user->hasAnyPermission(['chat_view', 'chat_manage', 'chat_admin']);
});

// ──────────────────────────────────────────────────────────────────────────
// 4. PRIVATE CHANNEL — antrian chat (queue)
//    Dipakai untuk: CustomerQueued, QueuePositionUpdated
//
//    Format: private-chat.queue
// ──────────────────────────────────────────────────────────────────────────
Broadcast::channel('chat.queue', function ($user) {
    // Hanya agent dengan permission chat_manage ke atas yang bisa lihat antrian
    return $user->hasAnyPermission(['chat_manage', 'chat_admin'])
        || $user->hasAnyRole(['admin', 'manager']);
});

// ──────────────────────────────────────────────────────────────────────────
// 5. PRESENCE CHANNEL — satu sesi chat (untuk tahu siapa yang sedang buka)
//    Opsional: berguna untuk "agent X sedang melihat chat ini"
//
//    Format: presence-chat.session.{sessionId}.viewers
// ──────────────────────────────────────────────────────────────────────────
Broadcast::channel('chat.session.{sessionId}.viewers', function ($user, $sessionId) {
    if ($user->hasAnyRole(['admin', 'manager'])) {
        return [
            'id'   => $user->id,
            'name' => $user->name,
            'role' => $user->getRoleNames()->first(),
        ];
    }

    if ($user->hasRole('staff')) {
        $isAssigned = ChatSessionAgent::where('chat_session_id', $sessionId)
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->exists();

        if ($isAssigned) {
            return [
                'id'   => $user->id,
                'name' => $user->name,
                'role' => 'staff',
            ];
        }
    }

    return false;
});

// ──────────────────────────────────────────────────────────────────────────
// 6. PRIVATE CHANNEL — customer (jika customer punya auth sendiri)
//    Dipakai untuk: status antrian customer, QueuePositionUpdated
//
//    Format: private-chat.customer.{sessionId}
// ──────────────────────────────────────────────────────────────────────────
Broadcast::channel('chat.customer.{sessionId}', function ($user, $sessionId) {
    $session = ChatSession::find($sessionId);

    if (! $session) {
        return false;
    }

    // Jika kamu menggunakan customer_id di tabel chat_sessions
    if (isset($session->customer_id)) {
        return (int) $session->customer_id === (int) $user->id;
    }

    // Jika customer tidak perlu auth (guest), skip channel ini
    // dan gunakan public channel atau token-based approach
    return false;
});