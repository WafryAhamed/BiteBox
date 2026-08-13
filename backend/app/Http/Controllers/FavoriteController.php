<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Favorite;
use App\Models\Product;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $favorites = Favorite::with(['product.category', 'product.addons'])
            ->where('user_id', $user->id)
            ->get()
            ->pluck('product');

        return $this->success(
            ProductResource::collection($favorites),
            'Favorite products retrieved successfully'
        );
    }

    public function toggle(Request $request, Product $product): JsonResponse
    {
        $user = $request->user();
        $existing = Favorite::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->first();

        if ($existing) {
            $existing->delete();
            return $this->success(['is_favorite' => false], 'Removed from favorites');
        }

        Favorite::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        return $this->success(['is_favorite' => true], 'Added to favorites');
    }
}
