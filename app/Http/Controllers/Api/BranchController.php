<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index()
    {
        $branches = Branch::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'address', 'city', 'province', 'instagram', 'google_maps_url', 'operating_hours', 'latitude', 'longitude', 'is_active'])
            ->map(function ($b) {
                $b->directions_url = "https://www.google.com/maps/dir/?api=1&destination={$b->latitude},{$b->longitude}";
                return $b;
            });

        return response()->json(['data' => $branches]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'address'          => 'nullable|string',
            'city'             => 'nullable|string|max:255',
            'province'         => 'nullable|string|max:255',
            'latitude'         => 'nullable|numeric',
            'longitude'        => 'nullable|numeric',
            'instagram'        => 'nullable|string|max:30|regex:/^@?[a-zA-Z0-9_.]+$/',
            'google_maps_url'  => 'nullable|url',
            'operating_hours'  => 'nullable|array',
            'is_active'        => 'boolean',
        ]);

        $branch = Branch::create($validated);

        return response()->json(['message' => 'Cabang berhasil dibuat.', 'data' => $branch], 201);
    }

    public function show($id)
    {
        $branch = Branch::findOrFail($id);
        return response()->json(['data' => $branch]);
    }

    public function update(Request $request, $id)
    {
        $branch = Branch::findOrFail($id);

        $validated = $request->validate([
            'name'             => 'sometimes|required|string|max:255',
            'address'          => 'nullable|string',
            'city'             => 'nullable|string|max:255',
            'province'         => 'nullable|string|max:255',
            'latitude'         => 'nullable|numeric',
            'longitude'        => 'nullable|numeric',
            'instagram'        => 'nullable|string|max:30|regex:/^@?[a-zA-Z0-9_.]+$/',
            'google_maps_url'  => 'nullable|url',
            'operating_hours'  => 'nullable|array',
            'is_active'        => 'boolean',
        ]);

        $branch->update($validated);

        return response()->json(['message' => 'Cabang berhasil diperbarui.', 'data' => $branch]);
    }

    public function destroy($id)
    {
        $branch = Branch::findOrFail($id);
        $branch->delete();

        return response()->json(['message' => 'Cabang berhasil dihapus.']);
    }

    public function bulkDelete(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'integer']);
        Branch::whereIn('id', $request->ids)->delete();

        return response()->json(['message' => 'Cabang berhasil dihapus.']);
    }

    public function cities()
    {
        $cities = Branch::where('is_active', true)
            ->select('city', 'province')
            ->distinct()
            ->orderBy('province')
            ->orderBy('city')
            ->get();

        return response()->json(['data' => $cities]);
    }

    public function nearest(Request $request)
    {
        $request->validate([
            'lat'   => 'required|numeric',
            'lng'   => 'required|numeric',
            'limit' => 'nullable|integer',
        ]);

        $lat    = (float) $request->lat;
        $lng    = (float) $request->lng;
        $limit  = (int)   $request->get('limit', 5);

        $branches = Branch::where('is_active', true)
            ->selectRaw("
                id, name, address, city, province, instagram,
                operating_hours, latitude, longitude,
                ( 6371 * ACOS(
                    COS(RADIANS(?)) * COS(RADIANS(latitude))
                    * COS(RADIANS(longitude) - RADIANS(?))
                    + SIN(RADIANS(?)) * SIN(RADIANS(latitude))
                )) AS distance_km
            ", [$lat, $lng, $lat])
            ->orderBy('distance_km')
            ->limit($limit)
            ->get()
            ->map(function ($b) {
                $b->directions_url = "https://www.google.com/maps/dir/?api=1&destination={$b->latitude},{$b->longitude}";
                return $b;
            });

        return response()->json(['data' => $branches]);
    }
}