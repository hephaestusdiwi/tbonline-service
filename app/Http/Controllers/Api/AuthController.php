<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Email atau password salah'], 401);
        }

        $user = Auth::user();
        
        if ($user->isSuspended()) {
            Auth::logout();
            return response()->json([
                'message' => 'Akun Anda telah disuspend. Hubungi administrator.',
            ], 403);
        }

        $user->update(['last_login_at' => now()]);
        AuditLog::record($user, 'login', "{$user->name} berhasil login");

        $token = $user->createToken('auth_token')->plainTextToken;

        // Set online langsung saat login
        \App\Models\UserOnlineStatus::updateOrCreate(
            ['user_id' => $user->id],
            ['is_online' => true, 'last_ping_at' => now()]
        );

        $onlineCount = \App\Models\UserOnlineStatus::where('is_online', true)
            ->where('last_ping_at', '>=', now()->subMinutes(2))
            ->count();

        broadcast(new \App\Events\AgentStatusChanged(
            anyOnline: $onlineCount > 0,
            onlineCount: $onlineCount
        ));

        return response()->json([
            'token' => $token,
            'user'  => [
                'id'          => $user->id,
                'name'        => $user->name,
                'email'       => $user->email,
                'role'        => $user->getRoleNames()->first(),
                'permissions' => $user->getAllPermissions()->pluck('name'),
                'avatar_url'  => $user->avatar ? asset('storage/' . $user->avatar) : null,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        // Set offline dulu sebelum token dihapus
        \App\Models\UserOnlineStatus::updateOrCreate(
            ['user_id' => $request->user()->id],
            ['is_online' => false, 'last_ping_at' => now()]
        );

        // Broadcast biar ChatWidget langsung update
        $onlineCount = \App\Models\UserOnlineStatus::where('is_online', true)
            ->where('last_ping_at', '>=', now()->subMinutes(2))
            ->count();

        broadcast(new \App\Events\AgentStatusChanged(
            anyOnline: $onlineCount > 0,
            onlineCount: $onlineCount
        ));

        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logout berhasil']);
    }

    public function me(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'id'          => $user->id,
            'name'        => $user->name,
            'email'       => $user->email,
            'role'        => $user->getRoleNames()->first(),
            'permissions' => $user->getAllPermissions()->pluck('name'),
            'avatar_url'  => $user->avatar ? asset('storage/' . $user->avatar) : null,
            'is_online'   => $user->onlineStatus?->is_online ?? false,
        ]);
    }
}