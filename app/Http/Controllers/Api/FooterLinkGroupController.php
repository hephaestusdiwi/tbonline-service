<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FooterLinkGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FooterLinkGroupController extends Controller
{
    public function index(): JsonResponse
    {
        $groups = FooterLinkGroup::with(['links' => fn ($q) => $q->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get();

        return response()->json($groups);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:100',
            'sort_order'    => 'nullable|integer|min:0',
        ]);

        $group = FooterLinkGroup::create([
            'name'          => $validated['name'],
            'sort_order'    => $validated['sort_order'] ?? 0,
        ]);

        return response()->json($group, 201);
    }

    public function update(Request $request, FooterLinkGroup $footerLinkGroup): JsonResponse
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:100',
            'sort_order'    => 'nullable|integer|min:0',
        ]);

        $footerLinkGroup->update($validated);

        return response()->json($footerLinkGroup);
    }

    public function destroy(FooterLinkGroup $footerLinkGroup): JsonResponse
    {
        $footerLinkGroup->delete();

        return response()->json(['message' => 'Grup berhasil dihapus']);
    }
}
