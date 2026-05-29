<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\FaqRequest;
use App\Models\Faq;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    use AuthorizesRequests;
    // ── PUBLIC ────────────────────────────────────────────────

    /**
     * GET /api/faqs  — tanpa auth, untuk halaman publik (Home)
     * Mengembalikan hanya FAQ aktif, dikelompokkan per kategori.
     */
    public function public(Request $request): JsonResponse
    {
        $faqs = Faq::active()
            ->ordered()
            ->select('id', 'question', 'answer', 'category')
            ->get()
            ->groupBy('category');

        return response()->json($faqs);
    }

    // ── ADMIN ─────────────────────────────────────────────────

    /**
     * GET /api/admin/faqs
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Faq::class);

        $query = Faq::with(['creator:id,name', 'updater:id,name'])
            ->ordered();

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($qb) use ($q) {
                $qb->where('question', 'like', "%{$q}%")
                   ->orWhere('answer', 'like', "%{$q}%")
                   ->orWhere('category', 'like', "%{$q}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
        }

        $faqs = $query->paginate($request->get('per_page', 15));

        return response()->json($faqs);
    }

    /**
     * POST /api/admin/faqs
     */
    public function store(FaqRequest $request): JsonResponse
    {
        $this->authorize('create', Faq::class);

        $faq = Faq::create([
            ...$request->validated(),
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        return response()->json($faq->load('creator:id,name'), 201);
    }

    /**
     * GET /api/admin/faqs/{faq}
     */
    public function show(Faq $faq): JsonResponse
    {
        $this->authorize('view', $faq);

        return response()->json($faq->load(['creator:id,name', 'updater:id,name']));
    }

    /**
     * PUT /api/admin/faqs/{faq}
     */
    public function update(FaqRequest $request, Faq $faq): JsonResponse
    {
        $this->authorize('update', $faq);

        $faq->update([
            ...$request->validated(),
            'updated_by' => auth()->id(),
        ]);

        return response()->json($faq->fresh(['creator:id,name', 'updater:id,name']));
    }

    /**
     * DELETE /api/admin/faqs/{faq}
     */
    public function destroy(Faq $faq): JsonResponse
    {
        $this->authorize('delete', $faq);

        $faq->delete();

        return response()->json(['message' => 'FAQ berhasil dihapus.']);
    }

    /**
     * PATCH /api/admin/faqs/reorder
     * Body: { items: [{ id: 1, order: 0 }, { id: 2, order: 1 }] }
     */
    public function reorder(Request $request): JsonResponse
    {
        $this->authorize('create', Faq::class); // Manager/Admin saja

        $request->validate([
            'items'         => ['required', 'array'],
            'items.*.id'    => ['required', 'integer', 'exists:faqs,id'],
            'items.*.order' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($request->items as $item) {
            Faq::where('id', $item['id'])->update(['order' => $item['order']]);
        }

        return response()->json(['message' => 'Urutan berhasil diperbarui.']);
    }

    /**
     * GET /api/admin/faqs/categories  — list kategori unik
     */
    public function categories(): JsonResponse
    {
        $cats = Faq::select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category')
            ->filter()
            ->values();

        return response()->json($cats);
    }
}