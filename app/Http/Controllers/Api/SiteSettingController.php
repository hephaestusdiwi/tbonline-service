<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SiteSettingController extends Controller
{
    /**
     * GET /api/settings
     */
    public function index(): JsonResponse
    {
        $settings = SiteSetting::all()
            ->map(fn ($s) => [
                'key'         => $s->key,
                'value'       => $s->type === 'image' && $s->value
                                    ? Storage::url($s->value)
                                    : $s->value,
                'type'        => $s->type,
                'label'       => $s->label,
                'description' => $s->description,
            ])
            ->keyBy('key');

        return response()->json($settings);
    }

    public function update(Request $request, string $key): JsonResponse
    {
        $request->validate([
            'value' => ['nullable', 'string', 'max:5000'],
        ]);

        $allowedKeys = [
            'google_site_verification',
            'site_name',
            'site_description',
            'site_keywords',
            'contact_address',
            'contact_phone',
            'contact_email',
            'contact_whatsapp',
            'social_facebook',
            'social_instagram',
            'social_tiktok',
            'social_twitter',
            'social_youtube',
            'social_linkedin',
            'shipping_couriers', 
            'admin_whatsapp',
            'store_whatsapp',
        ];

        if (!in_array($key, $allowedKeys)) {
            return response()->json(['message' => 'Setting key tidak diizinkan'], 403);
        }

        SiteSetting::updateOrCreate(
            ['key' => $key],
            ['value' => $request->input('value')]
        );

        return response()->json([
            'message' => 'Setting berhasil disimpan',
            'key'     => $key,
            'value'   => $request->input('value'),
        ]);
    }

    /**
     * POST /api/settings/logo
     */
    public function uploadLogo(Request $request): JsonResponse
    {
        $request->validate([
            'logo' => ['required', 'file', 'mimes:jpeg,jpg,png,gif,webp,svg', 'max:2048'],
        ], [
            'logo.required' => 'File logo wajib diupload',
            'logo.mimes'    => 'Format file harus jpeg, jpg, png, gif, webp atau svg',
            'logo.max'      => 'Ukuran file maksimal 2 MB',
        ]);

        $file        = $request->file('logo');
        $storagePath = $file->getClientOriginalExtension() === 'svg'
            ? $this->storeSvg($file)
            : $this->convertToWebp($file);

        $this->deleteOldLogo();

        SiteSetting::updateOrCreate(['key' => 'site_logo'], [
            'value'       => $storagePath,
            'type'        => 'image',
            'label'       => 'Logo Website',
            'description' => 'Logo utama yang tampil di header website',
        ]);

        return response()->json([
            'message' => 'Logo berhasil diperbarui',
            'url'     => Storage::url($storagePath),
            'path'    => $storagePath,
        ]);
    }

    public function deleteLogo(): JsonResponse
    {
        $this->deleteOldLogo();
        SiteSetting::set('site_logo', null);

        return response()->json(['message' => 'Logo berhasil dihapus']);
    }

    /**
     * POST /api/settings/logo-footer
     */
    public function uploadLogoFooter(Request $request): JsonResponse
    {
        $request->validate([
            'logo_footer' => ['required', 'file', 'mimes:jpeg,jpg,png,gif,webp,svg', 'max:2048'],
        ], [
            'logo_footer.required' => 'File logo footer wajib diupload',
            'logo_footer.mimes'    => 'Format file harus jpeg, jpg, png, gif, webp atau svg',
            'logo_footer.max'      => 'Ukuran file maksimal 2 MB',
        ]);

        $file        = $request->file('logo_footer');
        $storagePath = $file->getClientOriginalExtension() === 'svg'
            ? $this->storeSvg($file, 'footer')
            : $this->convertToWebp($file, 'footer');

        $this->deleteOldLogoFooter();

        SiteSetting::updateOrCreate(['key' => 'site_logo_footer'], [
            'value'       => $storagePath,
            'type'        => 'image',
            'label'       => 'Logo Footer',
            'description' => 'Logo yang tampil di bagian footer website',
        ]);

        return response()->json([
            'message' => 'Logo footer berhasil di update',
            'url'     => Storage::url($storagePath),
            'path'    => $storagePath,
        ]);
    }

    public function deleteLogoFooter(): JsonResponse
    {
        $this->deleteOldLogoFooter();
        SiteSetting::set('site_logo_footer', null);

        return response()->json(['message' => 'Logo footer berhasil dihapus']);
    }

    /**
     * POST /api/settings/favicon
     */
    public function uploadFavicon(Request $request): JsonResponse
    {
        $request->validate([
            'favicon' => ['required', 'file', 'mimes:png,jpg,jpeg', 'max:512'],
        ], [
            'favicon.required' => 'File favicon wajib diupload',
            'favicon.mimes'    => 'Format file harus PNG atau JPG',
            'favicon.max'      => 'Ukuran file maksimal 512 KB',
        ]);

        $file = $request->file('favicon');

        // Simpan sebagai PNG langsung (tidak dikonversi ke webp)
        $dest = 'logos/favicon_' . time() . '.png';

        $this->deleteOldFavicon();

        Storage::disk('public')->put($dest, file_get_contents($file->getRealPath()));

        SiteSetting::updateOrCreate(['key' => 'site_favicon'], [
            'value'       => $dest,
            'type'        => 'image',
            'label'       => 'Favicon',
            'description' => 'Ikon kecil yang tampil di browser tab',
        ]);

        return response()->json([
            'message' => 'Favicon berhasil diperbarui',
            'url'     => Storage::url($dest),
            'path'    => $dest,
        ]);
    }

    /**
     * DELETE /api/settings/favicon
     */
    public function deleteFavicon(): JsonResponse
    {
        $this->deleteOldFavicon();
        SiteSetting::where('key', 'site_favicon')->update(['value' => null]);

        return response()->json(['message' => 'Favicon berhasil dihapus']);
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function convertToWebp(UploadedFile $file, string $folder = 'logo'): string
    {
        $mime  = $file->getMimeType();
        $src   = $file->getRealPath();

        $image = match (true) {
            str_contains($mime, 'jpeg') => imagecreatefromjpeg($src),
            str_contains($mime, 'png')  => $this->pngWithAlpha($src),
            str_contains($mime, 'gif')  => imagecreatefromgif($src),
            str_contains($mime, 'webp') => imagecreatefromwebp($src),
            default                     => imagecreatefromjpeg($src),
        };

        $tmp = sys_get_temp_dir() . '/' . (string) Str::uuid() . '.webp';
        imagewebp($image, $tmp, 90);
        imagedestroy($image);

        $dest = "logos/{$folder}_" . time() . '.webp';
        Storage::disk('public')->put($dest, file_get_contents($tmp));
        @unlink($tmp);

        return $dest;
    }

    private function pngWithAlpha(string $path): \GdImage
    {
        $image = imagecreatefrompng($path);
        imagepalettetotruecolor($image);
        imagealphablending($image, true);
        imagesavealpha($image, true);
        return $image;
    }

    private function storeSvg(UploadedFile $file, string $folder = 'logo'): string
    {
        $dest = "logos/{$folder}_" . time() . '.svg';
        Storage::disk('public')->put($dest, file_get_contents($file->getRealPath()));
        return $dest;
    }

    public function deleteOldLogo(): void
    {
        $old = SiteSetting::get('site_logo');
        if ($old && Storage::disk('public')->exists($old)) {
            Storage::disk('public')->delete($old);
        }
    }

    public function deleteOldLogoFooter(): void
    {
        $old = SiteSetting::get('site_logo_footer');
        if ($old && Storage::disk('public')->exists($old)) {
            Storage::disk('public')->delete($old);
        }
    }

    private function deleteOldFavicon(): void
    {
        $old = SiteSetting::where('key', 'site_favicon')->value('value');
        if ($old && Storage::disk('public')->exists($old)) {
            Storage::disk('public')->delete($old);
        }
    }

    public function getShippingCouriers(): JsonResponse
    {
        $value = SiteSetting::get('shipping_couriers');

        $couriers = $value ? json_decode($value, true) : $this->defaultCouriers();

        return response()->json($couriers);
    }

    public function saveShippingCouriers(Request $request): JsonResponse
    {
        $request->validate([
            'couriers'          => ['required', 'array'],
            'couriers.*.name'   => ['required', 'string', 'max:100'],
            'couriers.*.logo'   => ['nullable', 'string', 'max:500'],
            'couriers.*.active' => ['required', 'boolean'],
        ]);

        $couriers = collect($request->couriers)->map(fn($c) => [
            'name'    => trim($c['name']),
            'logo'    => trim($c['logo'] ?? ''),
            'active'  => (bool) $c['active'],
        ])->values()->toArray();

        SiteSetting::updateOrCreate(
            ['key' => 'shipping_couriers'],
            [
                'value'       => json_encode($couriers),
                'type'        => 'json',
                'label'       => 'Jasa Pengiriman',
                'description' => 'Daftar kurir yang tampil di footer',
            ]
        );

        return response()->json([
            'message'  => 'Daftar pengiriman berhasil disimpan',
            'couriers' => $couriers,
        ]);
    }

    public function uploadCourierLogo(Request $request): JsonResponse
    {
        $request->validate([
            'logo' => ['required', 'file', 'mimes:jpeg,jpg,png,gif,webp,svg', 'max:2048'],
        ], [
            'logo.required' => 'File logo wajib diupload',
            'logo.mimes'    => 'Format file harus jpeg, jpg, png, gif, webp atau svg',
            'logo.max'      => 'Ukuran file maksimal 2 MB',
        ]);

        $file        = $request->file('logo');
        $storagePath = $file->getClientOriginalExtension() === 'svg'
            ? $this->storeSvg($file, 'courier')
            : $this->convertToWebp($file, 'courier');

        return response()->json([
            'message' => 'Logo kurir berhasil diupload',
            'url'     => Storage::url($storagePath),
            'path'    => $storagePath,
        ]);
    }

    private function defaultCouriers(): array
    {
        return [];
    }
}