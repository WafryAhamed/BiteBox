<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductAddon;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;
    private User $otherCustomer;
    private User $admin;
    private Category $category;
    private Product $product1;
    private Product $product2;
    private ProductAddon $addon1;
    private Address $address;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::factory()->create();
        $this->otherCustomer = User::factory()->create();
        $this->admin = User::factory()->admin()->create();

        $this->category = Category::factory()->create();

        $this->product1 = Product::factory()->create([
            'category_id' => $this->category->id,
            'name' => 'Chicken Crunch Burger',
            'price' => 850.00,
            'is_available' => true,
        ]);

        $this->addon1 = $this->product1->addons()->create([
            'name' => 'Extra Cheese',
            'price' => 100.00,
            'is_available' => true,
        ]);

        $this->product2 = Product::factory()->create([
            'category_id' => $this->category->id,
            'name' => 'Fries',
            'price' => 350.00,
            'is_available' => true,
        ]);

        $this->address = Address::create([
            'user_id' => $this->customer->id,
            'label' => 'Home',
            'full_name' => 'Kasun Perera',
            'phone' => '+94 77 245 6813',
            'address_line' => 'No. 18, Duplication Road',
            'city' => 'Colombo',
            'is_default' => true,
        ]);
    }

    public function test_customer_can_place_delivery_order_and_server_calculates_total(): void
    {
        // 2 x Chicken Crunch Burger (850 + 100 cheese = 950 each = 1900)
        // 1 x Fries (350)
        // Subtotal = 2250
        // Delivery fee = 200
        // Total = 2450
        $response = $this->actingAs($this->customer)
            ->postJson('/api/v1/orders', [
                'order_type' => 'DELIVERY',
                'payment_method' => 'CASH',
                'address_id' => $this->address->id,
                'special_instruction' => 'No onions please',
                'items' => [
                    [
                        'product_id' => $this->product1->id,
                        'quantity' => 2,
                        'addon_ids' => [$this->addon1->id],
                    ],
                    [
                        'product_id' => $this->product2->id,
                        'quantity' => 1,
                    ],
                ],
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'order_type' => 'DELIVERY',
                    'subtotal' => 2250.00,
                    'delivery_fee' => 200.00,
                    'total' => 2450.00,
                    'order_status' => 'PENDING',
                ],
            ]);

        $this->assertDatabaseHas('orders', [
            'user_id' => $this->customer->id,
            'subtotal' => 2250.00,
            'delivery_fee' => 200.00,
            'total' => 2450.00,
        ]);

        $this->assertDatabaseHas('order_items', [
            'product_name' => 'Chicken Crunch Burger',
            'quantity' => 2,
            'subtotal' => 1900.00,
        ]);

        $this->assertDatabaseHas('order_item_addons', [
            'addon_name' => 'Extra Cheese',
            'addon_price' => 100.00,
        ]);
    }

    public function test_pickup_order_has_zero_delivery_fee(): void
    {
        $response = $this->actingAs($this->customer)
            ->postJson('/api/v1/orders', [
                'order_type' => 'PICKUP',
                'payment_method' => 'CASH',
                'items' => [
                    [
                        'product_id' => $this->product2->id,
                        'quantity' => 2, // 2 x 350 = 700
                    ],
                ],
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'order_type' => 'PICKUP',
                    'subtotal' => 700.00,
                    'delivery_fee' => 0.00,
                    'total' => 700.00,
                ],
            ]);
    }

    public function test_delivery_order_requires_valid_address(): void
    {
        $response = $this->actingAs($this->customer)
            ->postJson('/api/v1/orders', [
                'order_type' => 'DELIVERY',
                'payment_method' => 'CASH',
                'items' => [
                    ['product_id' => $this->product2->id, 'quantity' => 1],
                ],
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['address_id']);
    }

    public function test_order_creation_fails_if_product_is_unavailable(): void
    {
        $this->product1->update(['is_available' => false]);

        $response = $this->actingAs($this->customer)
            ->postJson('/api/v1/orders', [
                'order_type' => 'PICKUP',
                'payment_method' => 'CASH',
                'items' => [
                    ['product_id' => $this->product1->id, 'quantity' => 1],
                ],
            ]);

        $response->assertStatus(400)
            ->assertJson(['success' => false]);
    }

    public function test_customer_can_view_own_orders_only(): void
    {
        // Order for customer
        $this->actingAs($this->customer)->postJson('/api/v1/orders', [
            'order_type' => 'PICKUP',
            'payment_method' => 'CASH',
            'items' => [['product_id' => $this->product2->id, 'quantity' => 1]],
        ]);

        // Order for other customer
        $this->actingAs($this->otherCustomer)->postJson('/api/v1/orders', [
            'order_type' => 'PICKUP',
            'payment_method' => 'CASH',
            'items' => [['product_id' => $this->product2->id, 'quantity' => 1]],
        ]);

        $response = $this->actingAs($this->customer)->getJson('/api/v1/orders');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data.items'));
    }

    public function test_customer_cannot_view_another_customers_order_detail(): void
    {
        $orderResponse = $this->actingAs($this->otherCustomer)->postJson('/api/v1/orders', [
            'order_type' => 'PICKUP',
            'payment_method' => 'CASH',
            'items' => [['product_id' => $this->product2->id, 'quantity' => 1]],
        ]);

        $orderId = $orderResponse->json('data.id');

        $response = $this->actingAs($this->customer)->getJson("/api/v1/orders/{$orderId}");
        $response->assertStatus(404);
    }

    public function test_customer_can_cancel_pending_order(): void
    {
        $orderResponse = $this->actingAs($this->customer)->postJson('/api/v1/orders', [
            'order_type' => 'PICKUP',
            'payment_method' => 'CASH',
            'items' => [['product_id' => $this->product2->id, 'quantity' => 1]],
        ]);

        $orderId = $orderResponse->json('data.id');

        $response = $this->actingAs($this->customer)->postJson("/api/v1/orders/{$orderId}/cancel");
        $response->assertStatus(200)
            ->assertJson(['data' => ['order_status' => 'CANCELLED']]);
    }

    public function test_admin_can_update_order_status(): void
    {
        $orderResponse = $this->actingAs($this->customer)->postJson('/api/v1/orders', [
            'order_type' => 'PICKUP',
            'payment_method' => 'CASH',
            'items' => [['product_id' => $this->product2->id, 'quantity' => 1]],
        ]);

        $orderId = $orderResponse->json('data.id');

        // PENDING -> CONFIRMED
        $response = $this->actingAs($this->admin)->patchJson("/api/v1/orders/{$orderId}/status", [
            'order_status' => 'CONFIRMED',
        ]);
        $response->assertStatus(200)->assertJson(['data' => ['order_status' => 'CONFIRMED']]);

        // CONFIRMED -> PREPARING
        $response = $this->actingAs($this->admin)->patchJson("/api/v1/orders/{$orderId}/status", [
            'order_status' => 'PREPARING',
        ]);
        $response->assertStatus(200)->assertJson(['data' => ['order_status' => 'PREPARING']]);
    }

    public function test_invalid_status_transition_is_rejected(): void
    {
        $orderResponse = $this->actingAs($this->customer)->postJson('/api/v1/orders', [
            'order_type' => 'PICKUP',
            'payment_method' => 'CASH',
            'items' => [['product_id' => $this->product2->id, 'quantity' => 1]],
        ]);

        $orderId = $orderResponse->json('data.id');

        // PENDING -> COMPLETED directly should be rejected
        $response = $this->actingAs($this->admin)->patchJson("/api/v1/orders/{$orderId}/status", [
            'order_status' => 'COMPLETED',
        ]);
        $response->assertStatus(422);
    }
}
