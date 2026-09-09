<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HomeVideo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HomeVideoController extends Controller
{
    private const SLOTS = ['video_only', 'video_with_caption'];

    // GET /api/home-videos — publik, dipakai admin panel & homepage sekaligus
    // (sama pola kayak /api/sliders & /api/categories).
    public function index()
    {
        return response()->json(HomeVideo::whereIn('slot', self::SLOTS)->get());
    }

    // PUT /api/home-videos/{slot}
    public function update(Request $request, string $slot)
    {
        if (!in_array($slot, self::SLOTS)) {
            return response()->json(['message' => 'Slot video tidak dikenali'], 404);
        }

        $homeVideo = HomeVideo::where('slot', $slot)->firstOrFail();

        $rules = [
            'is_active' => 'nullable|boolean',
            'video'     => 'nullable|file|max:204800|mimes:mp4,webm,mov',
        ];

        // Judul & deskripsi cuma relevan buat slot video_with_caption.
        if ($slot === 'video_with_caption') {
            $rules['title']       = 'nullable|string|max:255';
            $rules['description'] = 'nullable|string|max:2000';
        }

        $request->validate($rules);

        if ($request->hasFile('video')) {
            if ($homeVideo->video_path) {
                Storage::disk('public')->delete($homeVideo->video_path);
            }
            $homeVideo->video_path = $request->file('video')->store('home-videos', 'public');
        }

        if ($slot === 'video_with_caption') {
            $homeVideo->title       = $request->input('title', $homeVideo->title);
            $homeVideo->description = $request->input('description', $homeVideo->description);
        }

        $homeVideo->is_active = $request->input('is_active') ?? $homeVideo->is_active;
        $homeVideo->save();

        return response()->json($homeVideo->fresh());
    }
}