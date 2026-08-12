<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Services\CategoryService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CategoryController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly CategoryService $categoryService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $isAdmin = $request->user()?->isAdmin();
        $categories = $this->categoryService->getAll(activeOnly: !$isAdmin);

        if ($isAdmin) {
            $categories->loadCount('products');
        }

        return $this->success(
            CategoryResource::collection($categories),
            'Categories retrieved successfully'
        );
    }

    public function store(CategoryRequest $request): JsonResponse
    {
        Gate::authorize('create', Category::class);

        $category = $this->categoryService->create($request->validated());

        return $this->created(
            new CategoryResource($category),
            'Category created successfully'
        );
    }

    public function show(int $id): JsonResponse
    {
        $category = $this->categoryService->findOrFail($id);

        return $this->success(
            new CategoryResource($category),
            'Category retrieved successfully'
        );
    }

    public function update(CategoryRequest $request, int $id): JsonResponse
    {
        $category = $this->categoryService->findOrFail($id);
        Gate::authorize('update', $category);

        $category = $this->categoryService->update($category, $request->validated());

        return $this->success(
            new CategoryResource($category),
            'Category updated successfully'
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $category = $this->categoryService->findOrFail($id);
        Gate::authorize('delete', $category);

        $this->categoryService->delete($category);

        return $this->noContent('Category deleted successfully');
    }
}
