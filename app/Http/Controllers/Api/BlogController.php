<?php

namespace App\Http\Controllers\Api;
 
use App\Http\Controllers\Controller;
use App\Http\Resources\ContentResource;
use App\Models\Content;
use Illuminate\Http\Request;
 
class BlogController extends Controller
{
    // GET /api/blog?search=foo&tag=bar&per_page=9&page=1
    public function index(Request $request)
    {
        $query = Content::with('author')
            ->where('type', Content::TYPE_ARTICLE)
            ->where('status', Content::STATUS_PUBLISHED)
            ->when($request->search, fn ($q) => $q->search($request->search))
            ->when($request->tag,    fn ($q) => $q->whereJsonContains('tags', $request->tag))
            ->latest('published_at');
 
        $contents = $query->paginate($request->per_page ?? 9);
 
        return ContentResource::collection($contents);
    }
 
    // GET /api/blog/{slug}
    public function show(string $slug)
    {
        $content = Content::with('author')
            ->where('type', Content::TYPE_ARTICLE)
            ->where('status', Content::STATUS_PUBLISHED)
            ->where('slug', $slug)
            ->firstOrFail();
 
        return new ContentResource($content);
    }
}