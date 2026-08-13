<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use App\Http\Requests\VerifyOtpRequest;

class AuthController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly AuthService $authService
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());

        return $this->created([
            'user' => new UserResource($result['user']),
            'requires_verification' => true,
        ], 'Account created. Verification code sent to your email.');
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->validated());

        return $this->success([
            'user' => new UserResource($result['user']),
            'token' => $result['token'],
        ], 'Login successful');
    }

    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        $result = $this->authService->verifyOtp(
            $request->validated('email'),
            $request->validated('otp')
        );

        return $this->success([
            'user' => new UserResource($result['user']),
            'token' => $result['token'],
        ], 'Account verified successfully!');
    }

    public function resendOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        $this->authService->resendOtp($request->input('email'));

        return $this->success(null, 'Verification code sent to your email.');
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return $this->success(null, 'Logged out successfully');
    }

    public function me(Request $request): JsonResponse
    {
        return $this->success(
            new UserResource($request->user()),
            'User retrieved successfully'
        );
    }

    public function updateProfile(\App\Http\Requests\UpdateProfileRequest $request): JsonResponse
    {
        $updatedUser = $this->authService->updateProfile($request->user(), $request->validated());

        return $this->success(
            new UserResource($updatedUser),
            'Profile updated successfully'
        );
    }
}
