<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Support\Facades\Storage;

class ImageUploadController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'],
        ]);

        $path = $request->file('image')->store('editor-images', 'public');

        return response()->json([
            'url'  => asset('storage/' . $path),
            'path' => $path,
        ], 201);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate(['path' => ['required', 'string']]);

        $path = $request->input('path');

        if (! str_starts_with($path, 'editor-images/')) {
            return response()->json(['message' => 'Akses ditolak'], 403);
        }

        Storage::disk('public')->delete($path);

        return response()->json(['message' => 'Gambar berhasil dihapus']);
    }
}
