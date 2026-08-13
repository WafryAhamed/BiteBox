<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_update_profile_name_and_phone(): void
    {
        $user = User::factory()->create([
            'name' => 'Original Name',
            'phone' => '0770000000',
        ]);

        $response = $this->actingAs($user)->putJson('/api/v1/auth/profile', [
            'name' => 'Updated Name',
            'phone' => '0779998888',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'Updated Name',
                    'phone' => '0779998888',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'phone' => '0779998888',
        ]);
    }
}
