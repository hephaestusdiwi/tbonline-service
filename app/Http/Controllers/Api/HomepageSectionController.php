<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\HomepageSectionRequest;
use App\Http\Requests\HomepageSectionReorderRequest;
use App\Http\Resources\HomepageSectionResource;
use App\Models\HomepageSection;
use App\Services\HomepageSectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class HomepageSectionController extends Controller
{
    public function __construct(
        private readonly HomepageSectionService $service
    ) {}

    public function public(): JsonResponse
    {
        return response()->json($this->service->getPublicSections());
    }

    public function index(): AnonymousResourceCollection
    {
        $sections = $this->service->getAllForAdmin();

        return HomepageSectionResource::collection($sections);
    }

    public function store(HomepageSectionRequest $request): HomepageSectionResource
    {
        $section = $this->service->create($request->validate());

        return new HomepageSectionResource($section);
    }

    public function show(HomepageSection $section): HomepageSectionResource
    {
        return new HomepageSectionResource($section);
    }

    public function update(HomepageSectionRequest $request, HomepageSection $section): HomepageSectionResource
    {
        $updated = $this->service->update($section, $request->validated());

        return new HomepageSectionResource($updated);
    }

    public function toggle(HomepageSection $section): HomepageSectionResource
    {
        $updated = $this->service->toggleActive($section);

        return new HomepageSectionResource($updated);
    }

    public function reorder(HomepageSectionReorderRequest $request): JsonResponse
    {
        $this->service->reorder($request->validated('items'));

        return response()->json(['message' => 'Reordered success']);
    }

    public function destroy(HomepageSection $section): JsonResponse
    {
        $this->service->delete($section);

        return response()->json(['message' => 'Delete Success']);
    }
}
