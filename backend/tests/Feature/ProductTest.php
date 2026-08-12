<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $customer;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
        $this->customer = User::factory()->create();
        $this->category = Category::factory()->create();
    }

    public function test_anyone_can_list_available_products(): void
    {
        Product::factory()->count(3)->create(['category_id' => $this->category->id]);
        Product::factory()->unavailable()->create(['category_id' => $this->category->id]);

        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        // Customers only see available products
        $this->assertCount(3, $response->json('data.items'));
    }

    public function test_admin_sees_all_products_including_unavailable(): void
    {
        Product::factory()->count(3)->create(['category_id' => $this->category->id]);
        Product::factory()->unavailable()->create(['category_id' => $this->category->id]);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/v1/products');

        $response->assertStatus(200);
        $this->assertCount(4, $response->json('data.items'));
    }

    public function test_products_can_be_filtered_by_category(): void
    {
        $otherCategory = Category::factory()->create();
        Product::factory()->count(2)->create(['category_id' => $this->category->id]);
        Product::factory()->count(3)->create(['category_id' => $otherCategory->id]);

        $response = $this->getJson("/api/v1/products?category_id={$this->category->id}");

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data.items'));
    }

    public function test_products_can_be_searched(): void
    {
        Product::factory()->create([
            'category_id' => $this->category->id,
            'name' => 'Classic Burger',
        ]);
        Product::factory()->create([
            'category_id' => $this->category->id,
            'name' => 'Chicken Wings',
        ]);

        $response = $this->getJson('/api/v1/products?search=Burger');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data.items'));
    }

    public function test_products_are_paginated(): void
    {
        Product::factory()->count(20)->create(['category_id' => $this->category->id]);

        $response = $this->getJson('/api/v1/products?per_page=5');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'items',
                    'pagination' => ['current_page', 'last_page', 'per_page', 'total'],
                ],
            ]);

        $this->assertCount(5, $response->json('data.items'));
        $this->assertEquals(20, $response->json('data.pagination.total'));
    }

    public function test_admin_can_create_product(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/v1/products', [
                'category_id' => $this->category->id,
                'name' => 'New Product',
                'description' => 'A delicious item',
                'price' => 9.99,
                'preparation_time' => 10,
                'addons' => [
                    ['name' => 'Extra Cheese', 'price' => 1.50],
                ],
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => ['name' => 'New Product'],
            ]);

        $this->assertDatabaseHas('products', ['name' => 'New Product']);
        $this->assertDatabaseHas('product_addons', ['name' => 'Extra Cheese']);
    }

    public function test_customer_cannot_create_product(): void
    {
        $response = $this->actingAs($this->customer)
            ->postJson('/api/v1/products', [
                'category_id' => $this->category->id,
                'name' => 'Hack Product',
                'price' => 1.00,
            ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_update_product(): void
    {
        $product = Product::factory()->create(['category_id' => $this->category->id]);

        $response = $this->actingAs($this->admin)
            ->putJson("/api/v1/products/{$product->id}", [
                'category_id' => $this->category->id,
                'name' => 'Updated Product',
                'price' => 15.99,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => ['name' => 'Updated Product'],
            ]);
    }

    public function test_admin_can_delete_product(): void
    {
        $product = Product::factory()->create(['category_id' => $this->category->id]);

        $response = $this->actingAs($this->admin)
            ->deleteJson("/api/v1/products/{$product->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_admin_can_toggle_product_availability(): void
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'is_available' => true,
        ]);

        $response = $this->actingAs($this->admin)
            ->patchJson("/api/v1/products/{$product->id}/toggle-availability");

        $response->assertStatus(200)
            ->assertJson([
                'data' => ['is_available' => false],
            ]);

        // Toggle back
        $response = $this->actingAs($this->admin)
            ->patchJson("/api/v1/products/{$product->id}/toggle-availability");

        $response->assertJson([
            'data' => ['is_available' => true],
        ]);
    }

    public function test_customer_cannot_toggle_availability(): void
    {
        $product = Product::factory()->create(['category_id' => $this->category->id]);

        $response = $this->actingAs($this->customer)
            ->patchJson("/api/v1/products/{$product->id}/toggle-availability");

        $response->assertStatus(403);
    }

    public function test_can_view_product_with_addons(): void
    {
        $product = Product::factory()->create(['category_id' => $this->category->id]);
        $product->addons()->create(['name' => 'Extra Cheese', 'price' => 1.50]);

        $response = $this->getJson("/api/v1/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id', 'name', 'price', 'category', 'addons',
                ],
            ]);

        $this->assertCount(1, $response->json('data.addons'));
    }

    public function test_product_validation(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/v1/products', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'price', 'category_id']);
    }

    public function test_products_can_be_sorted(): void
    {
        Product::factory()->create([
            'category_id' => $this->category->id,
            'name' => 'Zebra Burger',
            'price' => 20,
        ]);
        Product::factory()->create([
            'category_id' => $this->category->id,
            'name' => 'Alpha Burger',
            'price' => 5,
        ]);

        $response = $this->getJson('/api/v1/products?sort_by=name&sort_dir=asc');

        $response->assertStatus(200);
        $items = $response->json('data.items');
        $this->assertEquals('Alpha Burger', $items[0]['name']);
    }
}
