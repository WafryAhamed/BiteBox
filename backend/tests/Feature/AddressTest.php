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
                'full_name' => 'Kasun Perera',
                'phone' => '+94 77 245 6813',
                'address_line' => 'No. 18, Duplication Road',
                'city' => 'Colombo 04',
                'notes' => 'Ring bell twice',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'label' => 'Home',
                    'full_name' => 'Kasun Perera',
                    'is_default' => true, // First address becomes default automatically
                ],
            ]);

        $this->assertDatabaseHas('addresses', [
            'user_id' => $this->customer->id,
            'full_name' => 'Kasun Perera',
        ]);
    }

    public function test_customer_can_list_own_addresses(): void
    {
        Address::create([
            'user_id' => $this->customer->id,
            'label' => 'Home',
            'full_name' => 'Kasun Perera',
            'phone' => '+94 77 245 6813',
            'address_line' => 'No. 18, Duplication Road',
            'city' => 'Colombo 04',
            'is_default' => true,
        ]);

        Address::create([
            'user_id' => $this->otherCustomer->id,
            'label' => 'Work',
            'full_name' => 'Hiruni Fernando',
            'phone' => '+94 71 638 4527',
            'address_line' => 'No. 24, Lewis Place',
            'city' => 'Negombo',
            'is_default' => true,
        ]);

        $response = $this->actingAs($this->customer)
            ->getJson('/api/v1/addresses');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('Kasun Perera', $response->json('data.0.full_name'));
    }

    public function test_customer_can_update_address(): void
    {
        $address = Address::create([
            'user_id' => $this->customer->id,
            'label' => 'Home',
            'full_name' => 'Kasun Perera',
            'phone' => '+94 77 245 6813',
            'address_line' => 'No. 18, Duplication Road',
            'city' => 'Colombo 04',
            'is_default' => true,
        ]);

        $response = $this->actingAs($this->customer)
            ->putJson("/api/v1/addresses/{$address->id}", [
                'full_name' => 'Kasun Perera',
                'phone' => '+94 77 245 6813',
                'address_line' => 'No. 18, Duplication Road',
                'city' => 'Kandy',
            ]);

        $response->assertStatus(200)
            ->assertJson(['data' => ['full_name' => 'Kasun Perera', 'city' => 'Kandy']]);
    }

    public function test_customer_cannot_update_other_customer_address(): void
    {
        $address = Address::create([
            'user_id' => $this->otherCustomer->id,
            'label' => 'Work',
            'full_name' => 'Hiruni Fernando',
            'phone' => '+94 71 638 4527',
            'address_line' => 'No. 24, Lewis Place',
            'city' => 'Negombo',
            'is_default' => true,
        ]);

        $response = $this->actingAs($this->customer)
            ->putJson("/api/v1/addresses/{$address->id}", [
                'full_name' => 'Hack Attempt',
                'phone' => '+94 77 245 6813',
                'address_line' => 'No. 18, Duplication Road',
                'city' => 'Colombo 04',
            ]);

        $response->assertStatus(403);
    }

    public function test_customer_can_delete_address(): void
    {
        $address = Address::create([
            'user_id' => $this->customer->id,
            'label' => 'Home',
            'full_name' => 'Kasun Perera',
            'phone' => '+94 77 245 6813',
            'address_line' => 'No. 18, Duplication Road',
            'city' => 'Colombo 04',
            'is_default' => true,
        ]);

        $response = $this->actingAs($this->customer)
            ->deleteJson("/api/v1/addresses/{$address->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('addresses', ['id' => $address->id]);
    }
}
