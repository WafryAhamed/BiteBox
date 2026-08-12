<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
        $this->customer = User::factory()->create();
    }

    public function test_anyone_can_list_categories(): void
    {
        Category::factory()->count(3)->create();
        Category::factory()->inactive()->create();

        // Unauthenticated user sees only active
        $response = $this->getJson('/api/v1/categories');
        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertCount(3, $response->json('data'));
    }

    public function test_admin_sees_all_categories_including_inactive(): void
    {
        Category::factory()->count(3)->create();
        Category::factory()->inactive()->create();

        $response = $this->actingAs($this->admin)
            ->getJson('/api/v1/categories');

        $response->assertStatus(200);
        $this->assertCount(4, $response->json('data'));
    }

    public function test_admin_can_create_category(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/v1/categories', [
                'name' => 'New Category',
                'description' => 'A test category',
                'is_active' => true,
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => ['name' => 'New Category'],
            ]);

        $this->assertDatabaseHas('categories', ['name' => 'New Category']);
    }

    public function test_customer_cannot_create_category(): void
    {
        $response = $this->actingAs($this->customer)
            ->postJson('/api/v1/categories', [
                'name' => 'Attempt',
                'description' => 'Should fail',
            ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_update_category(): void
    {
        $category = Category::factory()->create(['name' => 'Old Name']);

        $response = $this->actingAs($this->admin)
            ->putJson("/api/v1/categories/{$category->id}", [
                'name' => 'Updated Name',
                'description' => 'Updated description',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => ['name' => 'Updated Name'],
            ]);
    }

    public function test_customer_cannot_update_category(): void
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->customer)
            ->putJson("/api/v1/categories/{$category->id}", [
                'name' => 'Hack',
            ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_delete_category(): void
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->admin)
            ->deleteJson("/api/v1/categories/{$category->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_customer_cannot_delete_category(): void
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->customer)
            ->deleteJson("/api/v1/categories/{$category->id}");

        $response->assertStatus(403);
    }

    public function test_can_view_single_category(): void
    {
        $category = Category::factory()->create();

        $response = $this->getJson("/api/v1/categories/{$category->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => ['id' => $category->id],
            ]);
    }

    public function test_category_validation(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/v1/categories', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }
}
