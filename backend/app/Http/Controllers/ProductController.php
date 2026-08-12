<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\ProductService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProductController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly ProductService $productService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'category_id', 'is_available', 'sort_by', 'sort_dir', 'per_page']);

        // Non-admin users only see available products
        if (!$request->user()?->isAdmin()) {
            $filters['available_only'] = true;
        }

        $products = $this->productService->getAll($filters);

        return $this->success([
            'items' => ProductResource::collection($products->items()),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ], 'Products retrieved successfully');
    }

    public function store(ProductRequest $request): JsonResponse
    {
        Gate::authorize('create', Product::class);

        $product = $this->productService->create($request->validated());

        return $this->created(
            new ProductResource($product),
            'Product created successfully'
        );
    }

    public function show(int $id): JsonResponse
    {
        $product = $this->productService->findOrFail($id);

        return $this->success(
            new ProductResource($product),
            'Product retrieved successfully'
        );
    }

    public function update(ProductRequest $request, int $id): JsonResponse
    {
        $product = $this->productService->findOrFail($id);
        Gate::authorize('update', $product);

        $product = $this->productService->update($product, $request->validated());

        return $this->success(
            new ProductResource($product),
            'Product updated successfully'
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $product = $this->productService->findOrFail($id);
        Gate::authorize('delete', $product);

        $this->productService->delete($product);

        return $this->noContent('Product deleted successfully');
    }

    public function toggleAvailability(int $id): JsonResponse
    {
        $product = $this->productService->findOrFail($id);
        Gate::authorize('toggleAvailability', $product);

        $product = $this->productService->toggleAvailability($product);

        return $this->success(
            new ProductResource($product),
            'Product availability toggled successfully'
        );
    }
}
