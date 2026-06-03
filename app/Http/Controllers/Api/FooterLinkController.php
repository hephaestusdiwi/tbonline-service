<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FooterLink;
use App\Models\FooterLinkGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FooterLinkController extends Controller
{
    public function grouped(): JsonResponse
    {
        $groups = FooterLinkGroup::with(['links' => fn ($q) => $q->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get()
            ->map(fn ($g) => [
                'group_name' => $g->name,
                'links'      => $g->links,
            ]);

        return response()->json($groups);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'group_id'     => 'required|exists:footer_link_groups,id',
            'label'        => 'required|string|max:100',
            'url'          => 'required|string|max:500',
            'open_new_tab' => 'boolean',
            'sort_order'   => 'nullable|integer|min:0',
        ]);
 
        $link = FooterLink::create([
            'footer_link_group_id' => $validated['group_id'],
            'label'                => $validated['label'],
            'url'                  => $validated['url'],
            'open_new_tab'         => $validated['open_new_tab'] ?? false,
            'sort_order'           => $validated['sort_order'] ?? 0,
        ]);
 
        return response()->json($link, 201);
    }

    public function update(Request $request, FooterLink $footerLink): JsonResponse
    {
        $validated = $request->validate([
            'group_id'     => 'sometimes|exists:footer_link_groups,id',
            'label'        => 'required|string|max:100',
            'url'          => 'required|string|max:500',
            'open_new_tab' => 'boolean',
            'sort_order'   => 'nullable|integer|min:0',
        ]);
 
        if (isset($validated['group_id'])) {
            $validated['footer_link_group_id'] = $validated['group_id'];
            unset($validated['group_id']);
        }
 
        $footerLink->update($validated);
 
        return response()->json($footerLink);
    }

    public function destroy(FooterLink $footerLink): JsonResponse
    {
        $footerLink->delete();

        return response()->json(['message' => 'Link berhasil dihapus']);
    }
}
