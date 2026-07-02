<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;
use App\Models\SiteSetting;

class RajaOngkirController extends Controller
{
    private string $apiKey;
    private string $baseUrl = 'https://rajaongkir.komerce.id/api/v1';

    public function __construct()
    {
        $this->apiKey = config('services.rajaongkir.api_key');
    }

    public function searchDestination(Request $request)
    {
        $request->validate([
            'search' => 'required|string|min:3',
            'limit'  => 'nullable|integer|min:1|max:20',
        ]);
 
        try {
            $response = Http::withHeaders([
                'key' => $this->apiKey,
            ])->get("{$this->baseUrl}/destination/domestic-destination", [
                'search' => $request->search,
                'limit'  => $request->limit ?? 8,
                'offset' => 0,
            ]);
 
            if ($response->failed()) {
                return response()->json([
                    'message' => 'Gagal mengambil data destinasi.',
                    'error'   => $response->json(),
                ], $response->status());
            }
 
            return response()->json($response->json());
 
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan server.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

   /**
     * Calculate domestic shipping cost
     * POST /api/rajaongkir/shipping-cost
     * Body: { origin, destination, weight, price }
     *
     * Catatan: parameter "courier" TIDAK diterima dari client lagi.
     * Daftar kurir yang di-query ditentukan server dari Site Settings
     * (key: rajaongkir_active_couriers), supaya:
     *  1. Admin bisa toggle kurir aktif tanpa redeploy.
     *  2. Client tidak bisa maksa query kurir yang sudah dinonaktifkan.
     */
    public function shippingCost(Request $request)
    {
        $request->validate([
            'origin'      => 'required',
            'destination' => 'required',
            'weight'      => 'required|integer|min:1',
            'price'       => 'nullable|string|in:lowest,highest',
        ]);
 
        $courierString = $this->getActiveCourierString();
 
        if ($courierString === '') {
            return response()->json([
                'message' => 'Belum ada kurir aktif yang dikonfigurasi. Silakan atur di menu Site Settings > Pengiriman.',
            ], 422);
        }
 
        try {
            $response = Http::withHeaders([
                'key' => $this->apiKey,
            ])->asForm()->post("{$this->baseUrl}/calculate/domestic-cost", [
                'origin'      => $request->origin,
                'destination' => $request->destination,
                'weight'      => $request->weight,
                'courier'     => $courierString,
                'price'       => $request->price ?? 'lowest',
            ]);
 
            if ($response->failed()) {
                return response()->json([
                    'message' => 'Gagal mengambil data ongkos kirim.',
                    'error'   => $response->json(),
                ], $response->status());
            }
 
            return response()->json($response->json());
 
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan server.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    private function getActiveCourierString(): string
    {
        $validCodes = array_keys(config('rajaongkir.couriers'));

        $raw       = SiteSetting::get('rajaongkir_active_couriers');
        $active    = $raw ? (json_decode($raw, true) ?? []) : config('rajaongkir.default_active');

        $active    = array_values(array_intersect($active, $validCodes));

        return implode(':', $active);
    }
}