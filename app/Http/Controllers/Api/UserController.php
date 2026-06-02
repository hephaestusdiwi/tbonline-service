<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        $this->authorizeAdmin();

        $users = User::with('roles')
            ->orderBy('created_at', 'desc')   // pertahankan urutan lama
            ->get()
            ->map(fn(User $u) => $this->formatUser($u));

        return response()->json($users);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role'     => ['required', Rule::in(['admin', 'manager', 'staff'])],
        ]);

        $user = User::create([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'password'  => Hash::make($validated['password']),
            'is_active' => true,
        ]);

        $user->syncRoles([$validated['role']]);

        AuditLog::record($user, 'create', "Akun {$user->name} dibuat oleh " . auth()->user()->name);

        return response()->json($this->formatUser($user->load('roles')), 201);
    }

    public function show(User $user): JsonResponse   // route model binding
    {
        $this->authorizeAdmin();

        $user->load(['roles', 'auditLogs.actor']);

        return response()->json([
            ...$this->formatUser($user),
            'audit_logs' => $user->auditLogs->map(fn($log) => [
                'id'          => $log->id,
                'action'      => $log->action,
                'description' => $log->description,
                'actor_name'  => $log->actor?->name ?? 'System',
                'created_at'  => $log->created_at->toISOString(),
            ]),
        ]);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'role'  => ['required', Rule::in(['admin', 'manager', 'staff'])],
        ]);

        $user->update([
            'name'  => $validated['name'],
            'email' => $validated['email'],
        ]);

        if ($request->filled('password')) {
            $request->validate(['password' => 'min:6']);
            $user->update(['password' => Hash::make($request->password)]);
        }

        $user->syncRoles([$validated['role']]);

        AuditLog::record($user, 'update', "Data {$user->name} diperbarui oleh " . auth()->user()->name);

        return response()->json($this->formatUser($user->load('roles')));
    }

    public function resetPassword(Request $request, User $user): JsonResponse
    {
        $this->authorizeAdmin();

        $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user->update(['password' => Hash::make($request->password)]);

        // Paksa re-login — revoke semua active token milik user ini
        $user->tokens()->delete();

        AuditLog::record($user, 'reset_password', "Password {$user->name} direset oleh " . auth()->user()->name);

        return response()->json(['message' => 'Password berhasil direset.']);
    }

    public function suspend(User $user): JsonResponse
    {
        $this->authorizeAdmin();

        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'Tidak dapat mensuspend akun sendiri.'], 422);
        }

        if ($user->isSuspended()) {
            return response()->json(['message' => 'User sudah dalam status suspended.'], 422);
        }

        $user->suspend();
        $user->tokens()->delete();   // kick langsung

        AuditLog::record($user, 'suspend', "{$user->name} disuspend oleh " . auth()->user()->name);

        return response()->json([
            'message' => 'User berhasil disuspend.',
            'user'    => $this->formatUser($user),
        ]);
    }

    public function activate(User $user): JsonResponse
    {
        $this->authorizeAdmin();

        if (!$user->isSuspended()) {
            return response()->json(['message' => 'User sudah dalam status aktif.'], 422);
        }

        $user->activate();

        AuditLog::record($user, 'activate', "{$user->name} diaktifkan oleh " . auth()->user()->name);

        return response()->json([
            'message' => 'User berhasil diaktifkan.',
            'user'    => $this->formatUser($user),
        ]);
    }

    public function destroy(User $user): JsonResponse
    {
        $this->authorizeAdmin();

        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'Tidak bisa menghapus akun sendiri.'], 403);
        }

        $user->delete();

        return response()->json(['message' => 'User berhasil dihapus.']);
    }

    private function authorizeAdmin(): void
    {
        abort_if(
            !auth()->user()->isAdmin(),
            403,
            'Hanya admin yang dapat mengakses fitur ini.'
        );
    }

    private function formatUser(User $user): array
    {
        return [
            'id'            => $user->id,
            'name'          => $user->name,
            'email'         => $user->email,
            'role'          => $user->getRoleNames()->first() ?? '-',
            'is_active'     => (bool) $user->is_active,
            'status'        => $user->is_active ? 'active' : 'suspended',
            'last_login_at' => $user->last_login_at?->toISOString(),
            'avatar_url'    => $user->avatar ? asset('storage/' . $user->avatar) : null,
            'created_at'    => $user->created_at->toISOString(),
        ];
    }
}