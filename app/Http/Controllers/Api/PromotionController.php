<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PromotionController extends Controller
{
    public function index(): JsonResponse
    {
        $promotions = Promotion::where('is_active', true)
            ->orderBy('order')
            ->orderBy('created_at', 'desc')
            ->get(['id', 'title', 'image', 'link', 'link_type', 'order', 'is_active']);

        $promotions->each(fn ($p) => $p->append('image_url'));

        return response()->json([
            'data' => $promotions,
        ]);
    }

    public function adminIndex(): JsonResponse
    {
        $this->authorizeRole(['admin', 'manager']);

        $promotions = Promotion::orderBy('order')->orderBy('created_at', 'desc')->get();
        $promotions->each(fn ($p) => $p->append('image_url'));

        return response()->json(['data' => $promotions]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorizeRole(['admin', 'manager']);
 
        $validated = $request->validate([
            'title'     => 'required|string|max:255',
            'image'     => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
            'link'      => 'required|url|max:500',
            'link_type' => ['required', Rule::in(['instagram', 'artikel', 'other'])],
            'order'     => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);
 
        $path = $request->file('image')->store('promotions', 'public');
 
        $promotion = Promotion::create([
            'title'     => $validated['title'],
            'image'     => $path,
            'link'      => $validated['link'],
            'link_type' => $validated['link_type'],
            'order'     => $validated['order'] ?? 0,
            'is_active' => $validated['is_active'] ?? true,
        ]);
 
        $promotion->append('image_url');
 
        return response()->json([
            'message' => 'Promosi berhasil dibuat.',
            'data'    => $promotion,
        ], 201);
    }

    public function show(Promotion $promotion): JsonResponse
    {
        $this->authorizeRole(['admin', 'manager']);
        $promotion->append('image_url');

        return response()->json(['data' => $promotion]);
    }

    public function update(Request $request, Promotion $promotion): JsonResponse
    {
        $this->authorizeRole(['admin', 'manager']);

        $validated = $request->validate([
            'title'     => 'sometimes|string|max:255',
            'image'     => 'sometimes|image|mimes:jpg,jpeg,png,webp|max:5120',
            'link'      => 'sometimes|url|max:500',
            'link_type' => ['sometimes', Rule::in(['instagram', 'artikel', 'other'])],
            'order'     => 'sometimes|integer|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        if($request->hasFile('image')) {
            Storage::disk('public')->delete($promotion->image);
            $validated['image'] = $request->file('image')->store('promotions', 'public');
        }

        $promotion->update($validated);
        $promotion->append('image_url');

        return response()->json([
            'message' => 'Promosi berhasil diperbarui',
            'data'    => $promotion,
        ]);
    }

    public function destroy(Promotion $promotion): JsonResponse
    {
        $this->authorizeRole(['admin']);

        Storage::disk('public')->delete($promotion->image);
        $promotion->delete();

        return response()->json(['message' => 'Promosi berhasil dihapus']);
    }

    public function reorder(Request $request): JsonResponse
    {
        $this->authorizeRole(['admin', 'manager']);

        $request->validate([
            'items'         => 'required|array',
            'items.*.id'    => 'required|integer|exists:promotions,id',
            'items.*.order' => 'required|integer|min:0',
        ]);

        foreach ($request->items as $item) {
            Promotion::where('id', $item['id'])->update(['order' => $item['order']]);
        }

        return response()->json(['message' => 'Urutan promosi berhasil disimpan']);
    }

    // HELPER
    private function authorizeRole(array $roles): void
    {
        $user = auth()->user();
        if (! $user || ! $user->hasAnyRole($roles)) {
            abort(403, 'Akses ditolak');
        }
    }
}

