<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function register(array $data): array
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'phone' => $data['phone'] ?? null,
            'role' => UserRole::CUSTOMER,
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
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

        $token = $user->createToken('auth-token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    public function logout(User $user): void
    {
        $token = $user->currentAccessToken();
        if ($token && method_exists($token, 'delete')) {
            $token->delete();
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
