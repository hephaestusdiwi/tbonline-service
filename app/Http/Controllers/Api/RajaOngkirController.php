<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;

class RajaOngkirController extends Controller
{
    private string $apiKey;
    private string $baseUrl = 'https://rajaongkir.komerce.id/api/v1';

    public function __construct()
    {
        $this->apiKey = config('services.rajaongkir.api_key');
    }

    /**
     * Search destination (kelurahan/kecamatan)
     * GET /api/rajaongkir/search-destination?search=xxx&limit=8
     */
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
     * Body: { origin, destination, weight, courier, price }
     */
    public function shippingCost(Request $request)
    {
        $request->validate([
            'origin'      => 'required',
            'destination' => 'required',
            'weight'      => 'required|integer|min:1',
            'courier'     => 'required|string',
            'price'       => 'nullable|string|in:lowest,highest',
        ]);

        try {
            $response = Http::withHeaders([
                'key' => $this->apiKey,
            ])->asForm()->post("{$this->baseUrl}/calculate/domestic-cost", [
                'origin'      => $request->origin,
                'destination' => $request->destination,
                'weight'      => $request->weight,
                'courier'     => $request->courier,
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
}