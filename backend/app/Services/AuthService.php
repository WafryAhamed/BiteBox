<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        private readonly OtpService $otpService
    ) {}

    public function register(array $data): array
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone' => $data['phone'] ?? null,
            'role' => UserRole::CUSTOMER,
            'email_verified_at' => null,
        ]);

        $this->otpService->generateAndSendOtp($user->email, $user->name);

        return [
            'user' => $user,
            'requires_verification' => true,
        ];
    }

    public function login(array $credentials): array
    {
        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (is_null($user->email_verified_at)) {
            // Auto-send fresh OTP when unverified user attempts to log in
            try {
                $this->otpService->generateAndSendOtp($user->email, $user->name);
            } catch (\Throwable) {
                // Ignore rate limit exception if code was sent recently
            }

            throw ValidationException::withMessages([
                'email' => ['Please verify your email before logging in.'],
            ]);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    public function verifyOtp(string $email, string $otp): array
    {
        $user = $this->otpService->verifyOtp($email, $otp);

        $token = $user->createToken('auth-token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    public function resendOtp(string $email): bool
    {
        $user = User::where('email', $email)->first();
        if (! $user) {
            throw ValidationException::withMessages([
                'email' => ['User account not found with this email.'],
            ]);
        }

        return $this->otpService->generateAndSendOtp($user->email, $user->name);
    }

    public function logout(User $user): void
    {
        $token = $user->currentAccessToken();
        if ($token && method_exists($token, 'delete')) {
            $token->delete();
        } else {
            $user->tokens()->delete();
        }
    }

    public function updateProfile(User $user, array $data): User
    {
        if (!empty($data['password'])) {
            if (!empty($data['current_password']) && !Hash::check($data['current_password'], $user->password)) {
                throw ValidationException::withMessages([
                    'current_password' => ['The provided current password is incorrect.'],
                ]);
            }
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        unset($data['current_password']);

        $user->update(array_filter($data, fn($v) => $v !== null));

        return $user->fresh();
    }
}
