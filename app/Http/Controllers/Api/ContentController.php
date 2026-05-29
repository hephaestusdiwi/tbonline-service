<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContentRequest;
use App\Http\Requests\UpdateContentRequest;
use App\Http\Resources\ContentResource;
use App\Models\Content;
use App\Services\ContentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousRersourceCollection;

class ContentController extends Controller
{
    public function __construct(private readonly ContentService $service)
    {
        //
    }
    
    public function index(Request $request): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $filters = $request->only(['type', 'status', 'search', 'tag', 'per_page']);

        if ($request->user()->hasRole('staff')) {
            $filters['author_id'] = $request->user()->id;
        }

        $contents = $this->service->list($filters);

        return ContentResource::collection($contents);
    }

    public function store(StoreContentRequest $request): JsonResponse
    {
        if ($request->user()->hasRole('staff') && $request->validated('status') === Content::STATUS_PUBLISHED) {
            return response()->json([
                'message' => 'Staff hanya dapat menyimpan konten sebagai draft',
            ], 403);
        }

        $content = $this->service->create(
            $request->validated(),
            $request->user()->id
        );

        $content->load(['author', 'updater']);

        return (new ContentResource($content))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Content $content): ContentResource
    {
        $content->load(['author', 'updater']);

        return new ContentResource($content);
    }

    public function update(UpdateContentRequest $request, Content $content): ContentResource
    {
        // Cek: staff tidak bisa ubah status ke published
        if ($request->user()->hasRole('staff') && $request->validated('status') === Content::STATUS_PUBLISHED) {
            abort(403, 'Staff hanya dapat menyimpan sebagai draft.');
        }
 
        $updated = $this->service->update(
            $content,
            $request->validated(),
            $request->user()->id
        );
 
        return new ContentResource($updated);
    }

    public function destroy(Content $content): JsonResponse
    {
        $this->service->delete($content);

        return response()->json(['message' => 'Konten berhasil dihapus']);
    }

    public function publish(Request $request, Content $content): ContentResource|JsonResponse
    {
        $this->authorize('publish', $content);

        $updated = $this->service->togglePublish($content, $request->user()->id);

        return new ContentResource($updated);
    }

    public function showStatic(string $type): ContentResource|JsonResponse
    {
        if (! in_array($type, Content::STATIC_TYPES)) {
            return response()->json(['message' => 'Tipe konten tidak valid'], 422);
        }

        $content = Content::where('type', $type)->latest()->first();

        if (! $content) {
            return response()->json(['message' => 'Konten belum tersedia'], 404);
        }

        $content->load(['author', 'updater']);

        return new ContentResource($content);
    }
}
