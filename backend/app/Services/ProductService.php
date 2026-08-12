<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductService
{
    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = Product::with(['category', 'addons']);

        // Search
        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        // Category filter
        if (!empty($filters['category_id'])) {
            $query->byCategory($filters['category_id']);
        }

        // Availability filter
        if (isset($filters['is_available'])) {
            $available = filter_var($filters['is_available'], FILTER_VALIDATE_BOOLEAN);
            $query->where('is_available', $available);
        }

        // Available only for customers (non-admin)
        if (!empty($filters['available_only'])) {
            $query->available();
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDir = $filters['sort_dir'] ?? 'desc';
        $allowedSorts = ['name', 'price', 'created_at', 'preparation_time'];

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
        }

        $perPage = min((int) ($filters['per_page'] ?? 15), 50);

        return $query->paginate($perPage);
    }

    public function findOrFail(int $id): Product
    {
        return Product::with(['category', 'addons'])->findOrFail($id);
    }

    public function create(array $data): Product
    {
        $product = Product::create($data);

        if (!empty($data['addons'])) {
            foreach ($data['addons'] as $addon) {
                $product->addons()->create($addon);
            }
        }

        return $product->load(['category', 'addons']);
    }

    public function update(Product $product, array $data): Product
    {
        $product->update($data);

        if (isset($data['addons'])) {
            $product->addons()->delete();
            foreach ($data['addons'] as $addon) {
                $product->addons()->create($addon);
            }
        }

        return $product->fresh(['category', 'addons']);
    }

    public function delete(Product $product): void
    {
        $product->delete();
    }

    public function toggleAvailability(Product $product): Product
    {
        $product->update(['is_available' => !$product->is_available]);
        return $product->fresh(['category', 'addons']);
    }
}
