<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AddressTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;
    private User $otherCustomer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->customer = User::factory()->create();
        $this->otherCustomer = User::factory()->create();
    }

    public function test_customer_can_create_address(): void
    {
        $response = $this->actingAs($this->customer)
            ->postJson('/api/v1/addresses', [
                'label' => 'Home',
                'full_name' => 'John Doe',
                'phone' => '0771234567',
                'address_line' => '123 Main Street',
                'city' => 'Colombo',
                'notes' => 'Ring bell twice',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'label' => 'Home',
                    'full_name' => 'John Doe',
                    'is_default' => true, // First address becomes default automatically
                ],
            ]);

        $this->assertDatabaseHas('addresses', [
            'user_id' => $this->customer->id,
            'full_name' => 'John Doe',
        ]);
    }

    public function test_customer_can_list_own_addresses(): void
    {
        Address::create([
            'user_id' => $this->customer->id,
            'label' => 'Home',
            'full_name' => 'John Doe',
            'phone' => '0771234567',
            'address_line' => '123 Main Street',
            'city' => 'Colombo',
            'is_default' => true,
        ]);

        Address::create([
            'user_id' => $this->otherCustomer->id,
            'label' => 'Work',
            'full_name' => 'Jane Smith',
            'phone' => '0777654321',
            'address_line' => '456 Business Road',
            'city' => 'Colombo',
            'is_default' => true,
        ]);

        $response = $this->actingAs($this->customer)
            ->getJson('/api/v1/addresses');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('John Doe', $response->json('data.0.full_name'));
    }

    public function test_customer_can_update_address(): void
    {
        $address = Address::create([
            'user_id' => $this->customer->id,
            'label' => 'Home',
            'full_name' => 'John Doe',
            'phone' => '0771234567',
            'address_line' => '123 Main Street',
            'city' => 'Colombo',
            'is_default' => true,
        ]);

        $response = $this->actingAs($this->customer)
            ->putJson("/api/v1/addresses/{$address->id}", [
                'full_name' => 'John Updated',
                'phone' => '0771234567',
                'address_line' => '123 Main Street',
                'city' => 'Kandy',
            ]);

        $response->assertStatus(200)
            ->assertJson(['data' => ['full_name' => 'John Updated', 'city' => 'Kandy']]);
    }

    public function test_customer_cannot_update_other_customer_address(): void
    {
        $address = Address::create([
            'user_id' => $this->otherCustomer->id,
            'label' => 'Work',
            'full_name' => 'Jane Smith',
            'phone' => '0777654321',
            'address_line' => '456 Business Road',
            'city' => 'Colombo',
            'is_default' => true,
        ]);

        $response = $this->actingAs($this->customer)
            ->putJson("/api/v1/addresses/{$address->id}", [
                'full_name' => 'Hack Attempt',
                'phone' => '0771234567',
                'address_line' => '123 Main Street',
                'city' => 'Colombo',
            ]);

        $response->assertStatus(403);
    }

    public function test_customer_can_delete_address(): void
    {
        $address = Address::create([
            'user_id' => $this->customer->id,
            'label' => 'Home',
            'full_name' => 'John Doe',
            'phone' => '0771234567',
            'address_line' => '123 Main Street',
            'city' => 'Colombo',
            'is_default' => true,
        ]);

        $response = $this->actingAs($this->customer)
            ->deleteJson("/api/v1/addresses/{$address->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('addresses', ['id' => $address->id]);
    }
}
