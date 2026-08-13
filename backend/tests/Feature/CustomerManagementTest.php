<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_customer_list_and_details(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create([
            'name' => 'Kasun Perera',
            'email' => 'kasun.perera@bitebox.lk',
        ]);

        // Admin list customers
        $response = $this->actingAs($admin)->getJson('/api/v1/customers');
        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertGreaterThanOrEqual(1, count($response->json('data.items')));

        // Admin view customer detail
        $detailResponse = $this->actingAs($admin)->getJson("/api/v1/customers/{$customer->id}");
        $detailResponse->assertStatus(200)
            ->assertJson([
                'data' => [
                    'customer' => [
                        'name' => 'Kasun Perera',
                        'email' => 'kasun.perera@bitebox.lk',
                    ],
                ],
            ]);
    }

    public function test_non_admin_cannot_access_customer_management(): void
    {
        $customer = User::factory()->create();

        $response = $this->actingAs($customer)->getJson('/api/v1/customers');
        $response->assertStatus(403);
    }
}
