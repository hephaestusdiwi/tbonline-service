<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Services\Products\TopProductsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TopProductsController extends Controller
{
    public function __construct(private TopProductsService $service) {}

    // GET /api/homepage/top-products
    public function index(): JsonResponse
    {
        // Maksimal 6 produk.
        // Featured diprioritaskan, sisanya otomatis
        // diisi dari produk terlaris.
        $products = $this->service->getHomepageProducts();

        return response()->json([
            'data' => ProductResource::collection($products),
        ]);
    }

    // GET /api/admin/featured-products
    // List featured products untuk halaman admin
    public function adminList(): JsonResponse
    {
        $featured = $this->service->getFeaturedList();

        return response()->json([
            'data' => $featured->map(fn($f) => [
                'id'         => $f->id,
                'product_id' => $f->product_id,
                'sort_order' => $f->sort_order,
                'product'    => new ProductResource($f->product),
            ]),
        ]);
    }

    // POST /api/admin/featured-products
    // Set/replace semua featured products
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_ids'   => ['required', 'array', 'max:10'],
            'product_ids.*' => ['integer', 'exists:products,id'],
        ]);

        $this->service->setFeaturedProducts($validated['product_ids']);

        return response()->json([
            'message' => 'Featured products updated',
        ]);
    }

    // PATCH /api/admin/featured-products/reorder
    // Hanya mengubah urutan featured products
    public function reorder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids'   => ['required', 'array'],
            'ids.*' => ['integer', 'exists:featured_products,id'],
        ]);

        $this->service->reorderFeatured($validated['ids']);

        return response()->json([
            'message' => 'Order updated',
        ]);
    }
}