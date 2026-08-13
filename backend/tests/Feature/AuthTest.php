<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_register(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Pass@1234',
            'password_confirmation' => 'Pass@1234',
            'phone' => '+1234567890',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => ['id', 'name', 'email', 'phone', 'role'],
                    'requires_verification',
                ],
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'user' => [
                        'role' => 'customer',
                        'is_verified' => false,
                    ],
                    'requires_verification' => true,
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'role' => UserRole::CUSTOMER->value,
            'email_verified_at' => null,
        ]);
    }

    public function test_registration_rejects_weak_password(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Test User',
            'email' => 'weak@example.com',
            'password' => '12345',
            'password_confirmation' => '12345',
        ]);

        $response->assertStatus(422)
            ->assertJson(['success' => false]);
    }

    public function test_registration_validates_required_fields(): void
    {
        $response = $this->postJson('/api/v1/auth/register', []);

        $response->assertStatus(422)
            ->assertJson(['success' => false])
            ->assertJsonStructure(['errors']);
    }

    public function test_registration_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Test User',
            'email' => 'taken@example.com',
            'password' => 'Pass@1234',
            'password_confirmation' => 'Pass@1234',
        ]);

        $response->assertStatus(422);
    }

    public function test_unverified_user_cannot_login(): void
    {
        User::factory()->create([
            'email' => 'unverified@example.com',
            'password' => bcrypt('Pass@1234'),
            'email_verified_at' => null,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'unverified@example.com',
            'password' => 'Pass@1234',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_verified_user_can_login(): void
    {
        User::factory()->create([
            'email' => 'verified@example.com',
            'password' => bcrypt('Pass@1234'),
            'email_verified_at' => now(),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'verified@example.com',
            'password' => 'Pass@1234',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'data' => ['user', 'token'],
            ]);
    }

    public function test_login_fails_with_wrong_credentials(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('Pass@1234'),
            'email_verified_at' => now(),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422);
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($user)
            ->postJson('/api/v1/auth/logout');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_authenticated_user_can_get_profile(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($user)
            ->getJson('/api/v1/auth/me');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'email' => $user->email,
                ],
            ]);
    }

    public function test_unauthenticated_user_cannot_access_protected_routes(): void
    {
        $this->getJson('/api/v1/auth/me')->assertStatus(401);
        $this->postJson('/api/v1/auth/logout')->assertStatus(401);
    }

    public function test_full_registration_and_otp_verification_flow(): void
    {
        // 1. Register with strong password
        $regResponse = $this->postJson('/api/v1/auth/register', [
            'name' => 'John Verification',
            'email' => 'john.verify@example.com',
            'password' => 'SecurePass@2026',
            'password_confirmation' => 'SecurePass@2026',
            'phone' => '+1987654321',
        ]);

        $regResponse->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'requires_verification' => true,
                ],
            ]);

        // 2. Unverified login attempt should fail
        $loginFailResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'john.verify@example.com',
            'password' => 'SecurePass@2026',
        ]);

        $loginFailResponse->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Please verify your email before logging in.',
            ]);

        // 3. Incorrect OTP verification attempt should fail
        $badOtpResponse = $this->postJson('/api/v1/auth/verify-otp', [
            'email' => 'john.verify@example.com',
            'otp' => '000000',
        ]);

        $badOtpResponse->assertStatus(422);

        // 4. Retrieve stored OTP record from database
        $otpRecord = \Illuminate\Support\Facades\DB::table('otp_verifications')
            ->where('email', 'john.verify@example.com')
            ->first();

        $this->assertNotNull($otpRecord);

        // 5. Verify using a known code by seeding a fresh code
        \Illuminate\Support\Facades\DB::table('otp_verifications')
            ->where('email', 'john.verify@example.com')
            ->update([
                'otp_hash' => \Illuminate\Support\Facades\Hash::make('654321'),
                'expires_at' => now()->addMinutes(10),
            ]);

        $verifySuccessResponse = $this->postJson('/api/v1/auth/verify-otp', [
            'email' => 'john.verify@example.com',
            'otp' => '654321',
        ]);

        $verifySuccessResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'user' => [
                        'is_verified' => true,
                    ],
                ],
            ])
            ->assertJsonStructure([
                'data' => ['user', 'token'],
            ]);

        // 6. User should now be verified in DB
        $this->assertDatabaseHas('users', [
            'email' => 'john.verify@example.com',
        ]);
        $user = User::where('email', 'john.verify@example.com')->first();
        $this->assertNotNull($user->email_verified_at);

        // 7. Login should now succeed
        $loginSuccessResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'john.verify@example.com',
            'password' => 'SecurePass@2026',
        ]);

        $loginSuccessResponse->assertStatus(200)
            ->assertJson(['success' => true]);

        $token = $loginSuccessResponse->json('data.token');

        // 8. Access protected route with token
        $meResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/auth/me');

        $meResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'email' => 'john.verify@example.com',
                ],
            ]);

        // 9. Logout
        $logoutResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/auth/logout');

        $logoutResponse->assertStatus(200);

        // 10. Clear auth guards cache and access protected route after logout (should be 401)
        auth()->forgetGuards();
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/auth/me')
            ->assertStatus(401);
    }
}
