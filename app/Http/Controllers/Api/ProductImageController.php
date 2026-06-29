<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductImageController extends Controller
{
    /**
     * POST /api/products/upload-image
     * Upload foto produk, konversi ke WebP, simpan ke storage/public/products/
     */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|file|image|mimes:jpg,jpeg,png,webp|max:5120', // maks 5MB
        ]);

        $file      = $request->file('image');
        $extension = strtolower($file->getClientOriginalExtension());
        $tempPath  = $file->getRealPath();
        $filename  = Str::uuid() . '.webp';
        $storagePath = 'products/' . $filename;

        // Buat resource GD
        $image = match ($extension) {
            'jpg', 'jpeg' => imagecreatefromjpeg($tempPath),
            'png'         => imagecreatefrompng($tempPath),
            'webp'        => imagecreatefromwebp($tempPath),
            default       => null,
        };

        if (!$image) {
            return response()->json(['message' => 'Format file tidak didukung.'], 422);
        }

        // Pertahankan transparansi PNG
        if ($extension === 'png') {
            imagepalettetotruecolor($image);
            imagealphablending($image, true);
            imagesavealpha($image, true);
        }

        // Resize jika lebih dari 1200px (opsional, jaga performa)
        $origW = imagesx($image);
        $origH = imagesy($image);
        $maxW  = 1200;

        if ($origW > $maxW) {
            $ratio  = $maxW / $origW;
            $newW   = $maxW;
            $newH   = (int) round($origH * $ratio);
            $resized = imagecreatetruecolor($newW, $newH);

            // Pertahankan transparansi setelah resize
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
            imagefill($resized, 0, 0, $transparent);

            imagecopyresampled($resized, $image, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
            imagedestroy($image);
            $image = $resized;
        }

        // Encode ke WebP lalu simpan
        ob_start();
        imagewebp($image, null, 85);
        $webpData = ob_get_clean();
        imagedestroy($image);

        Storage::disk('public')->put($storagePath, $webpData);

        $url = Storage::disk('public')->url($storagePath);

        return response()->json([
            'path' => $storagePath,
            'url'  => $url,
        ], 201);
    }

    /**
     * DELETE /api/products/delete-image
     * Hapus foto produk dari storage
     */
    public function delete(Request $request): JsonResponse
    {
        $request->validate([
            'path' => 'required|string',
        ]);

        $path = $request->path;

        // Keamanan: pastikan path dalam folder products/ saja
        if (!str_starts_with($path, 'products/')) {
            return response()->json(['message' => 'Path tidak valid.'], 422);
        }

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }

        return response()->json(['message' => 'Foto berhasil dihapus.']);
    }
}