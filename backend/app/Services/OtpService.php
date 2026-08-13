<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class OtpService
{
    public function __construct(
        private readonly ResendService $resendService
    ) {}

    public function generateAndSendOtp(string $email, string $recipientName = 'Customer'): bool
    {
        // Rate-limit resend check: ensure at least 45 seconds between resend requests
        $recent = DB::table('otp_verifications')
            ->where('email', $email)
            ->orderByDesc('created_at')
            ->first();

        if ($recent && now()->diffInSeconds($recent->created_at) < 45) {
            $remaining = 45 - now()->diffInSeconds($recent->created_at);
            throw ValidationException::withMessages([
                'otp' => ["Please wait {$remaining} seconds before requesting a new code."],
            ]);
        }

        // Generate 6-digit OTP
        $otpCode = sprintf('%06d', random_int(0, 999999));
        $otpHash = Hash::make($otpCode);
        $expiresAt = now()->addMinutes(10);

        // Delete existing OTPs for this email
        DB::table('otp_verifications')->where('email', $email)->delete();

        // Save new OTP record
        DB::table('otp_verifications')->insert([
            'email' => $email,
            'otp_hash' => $otpHash,
            'attempts' => 0,
            'expires_at' => $expiresAt,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Send via Resend
        return $this->resendService->sendOtpEmail($email, $otpCode, $recipientName);
    }

    public function verifyOtp(string $email, string $otpCode): User
    {
        $record = DB::table('otp_verifications')
            ->where('email', $email)
            ->first();

        if (!$record) {
            throw ValidationException::withMessages([
                'otp' => ['No verification code requested. Please request a new code.'],
            ]);
        }

        // Check if expired
        if (now()->greaterThan($record->expires_at)) {
            throw ValidationException::withMessages([
                'otp' => ['This code has expired. Please request a new code.'],
            ]);
        }

        // Check attempt limit
        if ($record->attempts >= 5) {
            throw ValidationException::withMessages([
                'otp' => ['Too many attempts. Please request a new code later.'],
            ]);
        }

        // Increment attempts count
        DB::table('otp_verifications')
            ->where('id', $record->id)
            ->increment('attempts');

        // Check code match
        if (!Hash::check($otpCode, $record->otp_hash)) {
            throw ValidationException::withMessages([
                'otp' => ['That code is incorrect. Please try again.'],
            ]);
        }

        // Match success: find user, mark verified, delete OTP record
        $user = User::where('email', $email)->first();
        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['User account not found.'],
            ]);
        }

        $user->forceFill([
            'email_verified_at' => now(),
        ])->save();

        DB::table('otp_verifications')->where('id', $record->id)->delete();

        return $user->fresh();
    }
}
