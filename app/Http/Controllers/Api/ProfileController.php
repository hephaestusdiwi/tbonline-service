<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Requests\UpdatePasswordRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * GET /api/profile
     * Return authenticated user's profile data.
     */
    public function show(Request $request)
    {
        $user = $request->user()->load('roles');
        
        return response()->json([
            'id'            => $user->id,
            'name'          => $user->name,
            'email'         => $user->email,
            'is_active'     => $user->is_active,
            'avatar_url'    => $user->avatar
                ? Storage::disk('public')->url($user->avatar)
                : null,
            'roles'         => $user->getRoleNames(),
            'created_at'    => $user->created_at->toISOString(),
        ]);
    }

    /**
     * PUT /api/profile
     * Update name, email.
     */
    public function update(UpdateProfileRequest $request)
    {
        $user = $request->user();

        $user->update($request->validated());

        return response()->json([
            'message'   => 'Profile berhasil diperbarui',
            'user'      => [
                'id'        => $user->id,
                'name'      => $user->name,
                'email'     => $user->email,
                'is_active' => $user->is_active,
            ],
        ]);
    }

    /**
     * POST /api/profile/avatar
     * Upload & replace avatar. Stores to storage/app/public/avatars/.
     */
    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimetypes:image/jpeg,image/png,image/webp', 'max:2048'],
        ]);

        $user = $request->user();

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $path = $request->file('avatar')->store('avatars', 'public');

        $user->update(['avatar' => $path]);

        return response()->json([
            'message'       => 'Foto profile berhasil diperbarui',
            'avatar_url'    => Storage::disk('public')->url($path),
        ]);
    }

    /**
     * DELETE /api/profile/avatar
     * Remove avatar and reset to null.
     */
    public function deleteAvatar(Request $request)
    {
        $user = $request->user();

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
            $user->update(['avatar' => null]);
        }

        return response()->json(['message' => 'Foto profil berhasil dihapus']);
    }

    /**
     * PUT /api/profile/password
     * Change password — requires current_password verification.
     */
    public function updatePassword(UpdatePasswordRequest $request)
    {
        $user = $request->user();

        if (! Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message'   => 'Password saat ini tidak sesuai',
                'errors'    => ['current_password' => ['Password saat ini tidak sesuai']],  
            ], 422);
        }

        $user->update(['password' => Hash::make($request->new_password)]);

        return response()->json(['message' => 'Password berhasil diperbarui']);
    }
}
