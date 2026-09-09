<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    // GET /api/categories — publik, dipakai admin panel & homepage sekaligus
    // (sama kayak /api/sliders). Return semua kategori termasuk nonaktif;
    // yang nge-filter is_active itu tugas frontend publik (lihat CategoryList.vue).
    public function index()
    {
        $categories = Category::orderBy('order')->get();
        return response()->json($categories);
    }

    /**
     * Konversi foto ke WebP (kualitas 85) supaya ringan, konsisten dengan
     * cara Sliders nyimpen gambar. Ada fallback simpan file asli kalau
     * format nggak dikenali atau GD di environment ini nggak dukung WebP.
     */
    private function convertAndStoreAsWebp($file, string $directory = 'categories'): string
    {
        $originalExtension = strtolower($file->getClientOriginalExtension());
        $tempPath          = $file->getRealPath();
        $filename          = Str::uuid() . '.webp';
        $storagePath       = $directory . '/' . $filename;

        $image = match ($originalExtension) {
            'jpg', 'jpeg' => imagecreatefromjpeg($tempPath),
            'png'         => imagecreatefrompng($tempPath),
            'webp'        => imagecreatefromwebp($tempPath),
            default       => null,
        };

        if (!$image) {
            return $file->store($directory, 'public');
        }

        if (!function_exists('imagewebp')) {
            imagedestroy($image);
            return $file->store($directory, 'public');
        }

        if ($originalExtension === 'png') {
            imagepalettetotruecolor($image);
            imagealphablending($image, true);
            imagesavealpha($image, true);
        }

        ob_start();
        imagewebp($image, null, 85);
        $webpData = ob_get_clean();
        imagedestroy($image);

        Storage::disk('public')->put($storagePath, $webpData);

        return $storagePath;
    }

    // POST /api/categories
    public function store(Request $request)
    {
        $request->validate([
            'name'                  => 'required|string|max:255',
            'photo'                 => 'required|file|max:10240',
            'order'                 => 'nullable|integer',
            'product_categories'    => 'required|array|min:1',
            'product_categories.*'  => 'required|string|max:255',
        ]);

        $extension = strtolower($request->file('photo')->getClientOriginalExtension());
        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp'])) {
            return response()->json(['message' => 'File foto harus jpg, png, atau webp'], 422);
        }

        $category = Category::create([
            'name'                => $request->name,
            'product_categories'  => $request->product_categories,
            'photo_path'          => $this->convertAndStoreAsWebp($request->file('photo')),
            'order'               => $request->order ?? 0,
            'is_active'           => true,
        ]);

        return response()->json($category, 201);
    }

    // PUT /api/categories/{id} (dikirim via POST + _method=PUT, sama kayak Sliders)
    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $request->validate([
            'name'                  => 'sometimes|required|string|max:255',
            'order'                 => 'nullable|integer',
            'is_active'             => 'nullable|boolean',
            'photo'                 => 'nullable|file|max:10240',
            'product_categories'    => 'sometimes|required|array|min:1',
            'product_categories.*'  => 'required|string|max:255',
        ]);

        if ($request->hasFile('photo')) {
            $extension = strtolower($request->file('photo')->getClientOriginalExtension());
            if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp'])) {
                return response()->json(['message' => 'File foto harus jpg, png, atau webp'], 422);
            }

            if ($category->photo_path) {
                Storage::disk('public')->delete($category->photo_path);
            }

            $category->update(['photo_path' => $this->convertAndStoreAsWebp($request->file('photo'))]);
        }

        $category->update([
            'name'                => $request->input('name', $category->name),
            'product_categories'  => $request->input('product_categories', $category->product_categories),
            'order'               => $request->input('order', $category->order),
            'is_active'           => $request->input('is_active') ?? $category->is_active,
        ]);

        return response()->json($category->fresh());
    }

    // POST /api/categories/reorder
    public function reorder(Request $request)
    {
        $request->validate([
            'orders'         => 'required|array',
            'orders.*.id'    => 'required|exists:categories,id',
            'orders.*.order' => 'required|integer',
        ]);

        foreach ($request->orders as $item) {
            Category::where('id', $item['id'])->update(['order' => $item['order']]);
        }

        return response()->json(['message' => 'Urutan berhasil disimpan']);
    }

    // DELETE /api/categories/{id}
    public function destroy($id)
    {
        $category = Category::findOrFail($id);

        if ($category->photo_path) {
            Storage::disk('public')->delete($category->photo_path);
        }
        $category->delete();

        return response()->json(['message' => 'Kategori berhasil dihapus']);
    }
}