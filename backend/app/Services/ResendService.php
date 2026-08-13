<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ResendService
{
    private ?string $apiKey;
    private string $fromEmail;

    public function __construct()
    {
        $this->apiKey = config('services.resend.key') ?: env('RESEND_API_KEY');
        $this->fromEmail = env('MAIL_FROM_ADDRESS', 'onboarding@resend.dev');
    }

    public function sendOtpEmail(string $toEmail, string $otpCode, string $recipientName = 'Customer'): bool
    {
        $subject = "Your BiteBox Verification Code: {$otpCode}";
        $html = "
            <div style=\"font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; max-width: 500px; margin: 0 auto; padding: 24px; background-color: #0f0f12; color: #ffffff; border-radius: 12px; border: 1px solid #27272a;\">
                <div style=\"text-align: center; margin-bottom: 24px;\">
                    <h1 style=\"color: #ff2d55; font-size: 24px; font-weight: 800; margin: 0; letter-spacing: 2px;\">BITEBOX</h1>
                    <p style=\"color: #a1a1aa; font-size: 12px; text-transform: uppercase; tracking: 1px;\">Account Verification</p>
                </div>
                <div style=\"background-color: #18181b; padding: 20px; borderRadius: 8px; text-align: center; border: 1px solid #27272a;\">
                    <p style=\"color: #e4e4e7; font-size: 14px; margin-bottom: 16px;\">Hello <strong>" . htmlspecialchars($recipientName) . "</strong>,</p>
                    <p style=\"color: #a1a1aa; font-size: 13px; margin-bottom: 20px;\">Please use the 6-digit code below to verify your email address. This code expires in 10 minutes.</p>
                    <div style=\"font-size: 32px; font-weight: 800; color: #ff2d55; letter-spacing: 8px; padding: 12px 24px; background-color: #0f0f12; display: inline-block; border-radius: 8px; border: 1px dashed #ff2d55;\">
                        {$otpCode}
                    </div>
                </div>
                <p style=\"color: #71717a; font-size: 11px; text-align: center; margin-top: 24px;\">If you did not request this email, please ignore it.</p>
            </div>
        ";

        if (empty($this->apiKey)) {
            Log::info("Resend API key missing. Logging OTP for {$toEmail}: {$otpCode}");
            return true;
        }

        try {
            $response = Http::withToken($this->apiKey)
                ->acceptJson()
                ->post('https://api.resend.com/emails', [
                    'from' => $this->fromEmail,
                    'to' => [$toEmail],
                    'subject' => $subject,
                    'html' => $html,
                ]);

            if ($response->successful()) {
                Log::info("Resend email sent successfully to {$toEmail}");
                return true;
            }

            Log::error("Resend API error: " . $response->body());
            return false;
        } catch (\Throwable $e) {
            Log::error("Resend service exception: " . $e->getMessage());
            return false;
        }
    }
}
