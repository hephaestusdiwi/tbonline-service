<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SliderController extends Controller
{
    // GET /api/sliders
    public function index()
    {
        $sliders = Slider::orderBy('order')->get();
        return response()->json($sliders);
    }

    /**
     * Konversi gambar ke format WebP dan simpan ke storage.
     * Mengembalikan path file yang tersimpan.
     */
    private function convertAndStoreAsWebp($file, string $directory = 'sliders'): string
    {
        $originalExtension = strtolower($file->getClientOriginalExtension());
        $tempPath          = $file->getRealPath();
        $filename          = Str::uuid() . '.webp';
        $storagePath       = $directory . '/' . $filename;

        // Buat resource GD dari file asli
        $image = match ($originalExtension) {
            'jpg', 'jpeg' => imagecreatefromjpeg($tempPath),
            'png'         => imagecreatefrompng($tempPath),
            'webp'        => imagecreatefromwebp($tempPath),
            default       => null,
        };

        if (!$image) {
            // Fallback: simpan file apa adanya jika format tidak dikenali
            return $file->store($directory, 'public');
        }

        // Fallback juga kalau GD di environment ini nggak dukung encode WebP
        // (mis. dicompile tanpa --with-webp) — daripada 500, simpan aslinya aja.
        if (!function_exists('imagewebp')) {
            imagedestroy($image);
            return $file->store($directory, 'public');
        }

        // Pertahankan transparansi untuk PNG
        if ($originalExtension === 'png') {
            imagepalettetotruecolor($image);
            imagealphablending($image, true);
            imagesavealpha($image, true);
        }

        // Encode ke WebP di memory, lalu simpan via Storage facade
        ob_start();
        imagewebp($image, null, 85); // quality 85
        $webpData = ob_get_clean();
        imagedestroy($image);

        Storage::disk('public')->put($storagePath, $webpData);

        return $storagePath;
    }

    /**
     * Simpan file video langsung tanpa compress di server.
     * Staff wajib compress dulu di lokal (HandBrake/software lain)
     * sebelum upload.
     */
    private function storeVideo($file, string $directory = 'sliders'): string
    {
        return $file->store($directory, 'public');
    }

    // POST /api/sliders
    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'type'        => 'required|in:image,video',
            'file'        => 'required|file|max:204800',
            'file_mobile' => 'nullable|file|max:204800',
            'order'       => 'nullable|integer',
        ]);

        $file      = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());

        if ($request->type === 'image' && !in_array($extension, ['jpg', 'jpeg', 'png', 'webp'])) {
            return response()->json(['message' => 'File gambar harus jpg, png, atau webp'], 422);
        }

        if ($request->type === 'video' && !in_array($extension, ['mp4', 'webm'])) {
            return response()->json(['message' => 'File video harus mp4 atau webm'], 422);
        }

        if ($request->type === 'image') {
            $path = $this->convertAndStoreAsWebp($file);
        } else {
            $path = $this->storeVideo($file);
        }

        // Gambar mobile cuma relevan buat slide bertipe image — opsional,
        // kalau nggak diisi frontend fallback ke $path (gambar desktop).
        $mobilePath = null;
        if ($request->type === 'image' && $request->hasFile('file_mobile')) {
            $mobileFile = $request->file('file_mobile');
            $mobileExt  = strtolower($mobileFile->getClientOriginalExtension());

            if (!in_array($mobileExt, ['jpg', 'jpeg', 'png', 'webp'])) {
                return response()->json(['message' => 'File gambar mobile harus jpg, png, atau webp'], 422);
            }

            $mobilePath = $this->convertAndStoreAsWebp($mobileFile);
        }

        $slider = Slider::create([
            'title'            => $request->title,
            'type'             => $request->type,
            'file_path'        => $path,
            'file_path_mobile' => $mobilePath,
            'order'            => $request->order ?? 0,
            'is_active'        => true,
            'is_processing'    => false,
        ]);

        return response()->json($slider, 201);
    }

    // PUT /api/sliders/{id}
    public function update(Request $request, $id)
    {
        $slider = Slider::findOrFail($id);

        $request->validate([
            'title'         => 'sometimes|required|string|max:255',
            'order'         => 'nullable|integer',
            'is_active'     => 'nullable|boolean',
            'file'          => 'nullable|file|max:204800',
            'file_mobile'   => 'nullable|file|max:204800',
            'remove_mobile' => 'nullable|boolean',
        ]);

        if ($request->hasFile('file')) {
            Storage::disk('public')->delete($slider->file_path);

            $file      = $request->file('file');
            $extension = strtolower($file->getClientOriginalExtension());
            $isVideo   = in_array($extension, ['mp4', 'webm']);

            if ($isVideo) {
                $path = $this->storeVideo($file);

                $slider->update([
                    'type'          => 'video',
                    'file_path'     => $path,
                    'is_processing' => false,
                ]);

                // Slide jadi video → gambar mobile lama (kalau ada) udah nggak relevan.
                if ($slider->file_path_mobile) {
                    Storage::disk('public')->delete($slider->file_path_mobile);
                    $slider->update(['file_path_mobile' => null]);
                }
            } else {
                $path = $this->convertAndStoreAsWebp($file);

                $slider->update([
                    'type'          => 'image',
                    'file_path'     => $path,
                    'is_processing' => false,
                ]);
            }
        }

        // Ganti/hapus gambar mobile — independen dari file desktop di atas.
        if ($slider->type === 'image' && $request->hasFile('file_mobile')) {
            $mobileFile = $request->file('file_mobile');
            $mobileExt  = strtolower($mobileFile->getClientOriginalExtension());

            if (!in_array($mobileExt, ['jpg', 'jpeg', 'png', 'webp'])) {
                return response()->json(['message' => 'File gambar mobile harus jpg, png, atau webp'], 422);
            }

            if ($slider->file_path_mobile) {
                Storage::disk('public')->delete($slider->file_path_mobile);
            }

            $slider->update(['file_path_mobile' => $this->convertAndStoreAsWebp($mobileFile)]);
        } elseif ($request->boolean('remove_mobile') && $slider->file_path_mobile) {
            Storage::disk('public')->delete($slider->file_path_mobile);
            $slider->update(['file_path_mobile' => null]);
        }

        $slider->update([
            'title'     => $request->input('title', $slider->title),
            'order'     => $request->input('order', $slider->order),
            'is_active' => $request->input('is_active') ?? $slider->is_active,
        ]);

        return response()->json($slider->fresh());
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'orders'          => 'required|array',
            'orders.*.id'     => 'required|exists:sliders,id',
            'orders.*.order'  => 'required|integer',
        ]);

        foreach ($request->orders as $item) {
            Slider::where('id', $item['id'])->update(['order' => $item['order']]);
        }

        return response()->json(['message' => 'Urutan berhasil disimpan']);
    }

    // DELETE /api/sliders/{id}
    public function destroy($id)
    {
        $slider = Slider::findOrFail($id);

        Storage::disk('public')->delete($slider->file_path);
        if ($slider->file_path_mobile) {
            Storage::disk('public')->delete($slider->file_path_mobile);
        }
        $slider->delete();

        return response()->json(['message' => 'Slider berhasil dihapus']);
    }
}