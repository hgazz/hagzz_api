<?php

namespace App\Services\Chataman;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class ChatamanService
{
    public function sendOtp(string $phoneNumber, string $otp, string $locale = 'en'): array
    {
        $token = trim((string) config('services.chataman.token'));

        if (!$token) {
            throw new RuntimeException('Chataman access token is not configured.');
        }

        $tokenPrefix = trim((string) config('services.chataman.token_prefix', 'Bearer'));
        $tokenValue = $tokenPrefix ? "{$tokenPrefix} {$token}" : $token;
        $templateParameter = [
            'type' => 'text',
            'text' => $otp,
        ];

        $response = Http::baseUrl(rtrim(config('services.chataman.base_url'), '/'))
            ->acceptJson()
            ->asJson()
            ->withHeaders([
                config('services.chataman.token_header', 'Authorization') => $tokenValue,
            ])
            ->timeout((int) config('services.chataman.timeout', 15))
            ->post('/api/send/template', [
                'phone' => $this->normalizePhoneNumber($phoneNumber),
                'template' => [
                    'name' => config('services.chataman.otp_template_name', 'otp_el7lmplatform'),
                    'language' => [
                        'code' => config('services.chataman.otp_template_language', 'ar'),
                    ],
                    'components' => [
                        [
                            'type' => 'body',
                            'parameters' => [$templateParameter],
                        ],
                        [
                            'type' => 'button',
                            'sub_type' => 'url',
                            'index' => '0',
                            'parameters' => [$templateParameter],
                        ],
                    ],
                ],
            ]);

        if (!$response->successful()) {
            throw new RuntimeException(
                "Chataman request failed with status {$response->status()}: {$response->body()}"
            );
        }

        $payload = $response->json();
        $responseData = $payload['data'] ?? [];
        $providerData = $responseData['data'] ?? [];

        if (
            !is_array($payload)
            || ($payload['statusCode'] ?? null) !== 200
            || ($responseData['success'] ?? false) !== true
            || ($responseData['reached_meta'] ?? false) !== true
        ) {
            throw new RuntimeException('Chataman did not confirm that the message reached Meta.');
        }

        Log::info('Chataman accepted OTP message', [
            'message_id' => $providerData['messages'][0]['id'] ?? null,
            'provider_status' => $providerData['chat'][0]['value']['status'] ?? null,
            'reached_meta' => true,
        ]);

        return $payload;
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
