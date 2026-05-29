<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

/**
 * ProductImageService
 *
 * Bertanggung jawab untuk:
 *   1. Download gambar dari URL eksternal (e.g. Olsera CDN)
 *   2. Convert ke format WebP
 *   3. Compress (quality configurable, default 80)
 *   4. Simpan ke storage/app/public/products/{subfolder}/
 *   5. Return path relatif yang siap disimpan ke DB
 *
 * Usage:
 *   $service = app(ProductImageService::class);
 *   $path    = $service->downloadAndConvert('https://cdn.olsera.com/img.jpg', 'products/123');
 *   // returns: "products/123/a1b2c3d4.webp"  (relative to storage/app/public/)
 *   // or null jika gagal
 *
 * Public URL:
 *   asset('storage/' . $path)   →  https://yourdomain.com/storage/products/123/a1b2c3d4.webp
 */
class ProductImageService
{
    /**
     * Subfolder root di dalam storage/app/public/
     * Ubah jika ingin path berbeda.
     */
    private const BASE_DISK  = 'public';
    private const BASE_FOLDER = 'products';

    /**
     * WebP quality (0–100).
     * 80 = balance antara ukuran dan kualitas.
     */
    private const WEBP_QUALITY = 80;

    /**
     * Resize maksimum panjang sisi (px). null = tidak di-resize.
     * Berguna untuk mencegah gambar terlalu besar masuk storage.
     */
    private const MAX_DIMENSION = 1200;

    /**
     * Timeout HTTP download (detik).
     */
    private const DOWNLOAD_TIMEOUT = 30;

    /**
     * Download gambar dari $url, convert ke WebP, simpan ke storage.
     *
     * @param  string      $url       URL gambar eksternal
     * @param  string|null $subfolder Sub-direktori tambahan, e.g. "products/123"
     *                                Default: "products"
     * @return string|null            Path relatif (dari disk 'public') atau null jika gagal
     */
    public function downloadAndConvert(string $url, ?string $subfolder = null): ?string
    {
        // Normalisasi URL — skip jika sudah local path atau kosong
        $url = trim($url);
        if ($url === '' || $this->isLocalPath($url)) {
            return $url ?: null;
        }

        try {
            // 1. Download
            $imageData = $this->fetchRemoteImage($url);
            if ($imageData === null) {
                return null;
            }

            // 2. Buat Intervention Image dari raw bytes
            $image = Image::read($imageData);

            // 3. Resize jika terlalu besar (maintain aspect ratio)
            $this->resizeIfNeeded($image);

            // 4. Encode ke WebP
            $encoded = $image->toWebp(quality: self::WEBP_QUALITY);

            // 5. Tentukan path tujuan
            $folder   = $this->resolveFolder($subfolder);
            $filename = Str::uuid()->toString() . '.webp';
            $path     = $folder . '/' . $filename;

            // 6. Simpan ke disk 'public' (storage/app/public/)
            Storage::disk(self::BASE_DISK)->put($path, (string) $encoded);

            return $path;

        } catch (\Throwable $e) {
            Log::warning('[ProductImageService] Gagal memproses gambar', [
                'url'   => $url,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Batch download untuk array URL foto produk (photo_1 s.d. photo_10).
     *
     * @param  array       $photos    ['photo_1' => 'https://...', 'photo_2' => '...', ...]
     * @param  string|null $subfolder
     * @return array                  Array dengan key yang sama, value = local path / null
     */
    public function downloadProductPhotos(array $photos, ?string $subfolder = null): array
    {
        $result = [];
        foreach ($photos as $key => $url) {
            $result[$key] = empty($url) ? null : $this->downloadAndConvert($url, $subfolder);
        }
        return $result;
    }

    /**
     * Hapus file dari storage berdasarkan path relatif.
     * Berguna saat update/replace produk.
     */
    public function delete(string $path): bool
    {
        return Storage::disk(self::BASE_DISK)->delete($path);
    }

    // ─────────────────────────────────────────────────────────────
    // Private helpers
    // ─────────────────────────────────────────────────────────────

    /**
     * Download raw bytes gambar dari URL.
     * Return null jika response bukan HTTP 200 atau Content-Type bukan image/*.
     */
    private function fetchRemoteImage(string $url): ?string
    {
        try {
            $response = Http::timeout(self::DOWNLOAD_TIMEOUT)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; ProductImporter/1.0)'])
                ->get($url);

            if (! $response->successful()) {
                Log::warning('[ProductImageService] HTTP gagal', [
                    'url'    => $url,
                    'status' => $response->status(),
                ]);
                return null;
            }

            // Validasi Content-Type harus image
            $contentType = $response->header('Content-Type') ?? '';
            if (! str_starts_with($contentType, 'image/')) {
                Log::warning('[ProductImageService] Bukan Content-Type image', [
                    'url'          => $url,
                    'content_type' => $contentType,
                ]);
                return null;
            }

            return $response->body();

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::warning('[ProductImageService] Koneksi gagal', [
                'url'   => $url,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Resize gambar jika lebar atau tinggi melebihi MAX_DIMENSION.
     * Menggunakan contain (tidak crop) agar aspek rasio tetap.
     */
    private function resizeIfNeeded(\Intervention\Image\Interfaces\ImageInterface $image): void
    {
        if (self::MAX_DIMENSION === null) {
            return;
        }

        $width  = $image->width();
        $height = $image->height();

        if ($width > self::MAX_DIMENSION || $height > self::MAX_DIMENSION) {
            $image->scaleDown(
                width:  self::MAX_DIMENSION,
                height: self::MAX_DIMENSION,
            );
        }
    }

    /**
     * Resolve folder tujuan.
     * Jika subfolder diisi, gabungkan dengan BASE_FOLDER.
     */
    private function resolveFolder(?string $subfolder): string
    {
        $base = self::BASE_FOLDER;

        if ($subfolder === null || trim($subfolder) === '') {
            return $base;
        }

        // Bersihkan path traversal
        $safe = ltrim(str_replace(['..', '\\'], ['', '/'], $subfolder), '/');

        return $base . '/' . $safe;
    }

    /**
     * Periksa apakah string sudah merupakan path lokal
     * (bukan URL eksternal).
     */
    private function isLocalPath(string $path): bool
    {
        return ! str_starts_with($path, 'http://') && ! str_starts_with($path, 'https://');
    }
}