<?php

namespace App\Services\Chataman;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class ChatamanService
{
    public function sendOtp(string $phoneNumber, string $otp, string $locale = 'en'): array
    {
        $token = config('services.chataman.token');

        if (!$token) {
            throw new RuntimeException('Chataman access token is not configured.');
        }

        $message = $locale === 'ar'
            ? "رمز التحقق الخاص بك في Hagzz هو: {$otp}"
            : "Your Hagzz verification code is: {$otp}";

        $tokenPrefix = trim((string) config('services.chataman.token_prefix', 'Bearer'));
        $tokenValue = $tokenPrefix ? "{$tokenPrefix} {$token}" : $token;

        $response = Http::baseUrl(rtrim(config('services.chataman.base_url'), '/'))
            ->acceptJson()
            ->asJson()
            ->withHeaders([
                config('services.chataman.token_header', 'Authorization') => $tokenValue,
            ])
            ->timeout((int) config('services.chataman.timeout', 15))
            ->post('/api/send', [
                'phone' => $this->normalizePhoneNumber($phoneNumber),
                'message' => $message,
            ]);

        if (!$response->successful()) {
            throw new RuntimeException(
                "Chataman request failed with status {$response->status()}: {$response->body()}"
            );
        }

        return $response->json() ?? [];
    }

    private function normalizePhoneNumber(string $phoneNumber): string
    {
        $phoneNumber = preg_replace('/[^\d+]/', '', trim($phoneNumber));

        if (str_starts_with($phoneNumber, '00')) {
            return '+' . substr($phoneNumber, 2);
        }

        return str_starts_with($phoneNumber, '+')
            ? $phoneNumber
            : '+' . ltrim($phoneNumber, '0');
    }
}
