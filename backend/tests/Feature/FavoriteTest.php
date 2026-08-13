<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_toggle_favorite_product(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        // Toggle ON
        $response = $this->actingAs($user)->postJson("/api/v1/favorites/{$product->id}/toggle");
        $response->assertStatus(200)
            ->assertJson(['data' => ['is_favorite' => true]]);

        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        // Get favorites list
        $listResponse = $this->actingAs($user)->getJson('/api/v1/favorites');
        $listResponse->assertStatus(200);
        $this->assertCount(1, $listResponse->json('data'));

        // Toggle OFF
        $toggleOffResponse = $this->actingAs($user)->postJson("/api/v1/favorites/{$product->id}/toggle");
        $toggleOffResponse->assertStatus(200)
            ->assertJson(['data' => ['is_favorite' => false]]);

        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
    }
}
