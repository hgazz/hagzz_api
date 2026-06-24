<?php

namespace Tests\Unit\Services;

use App\Services\Chataman\ChatamanService;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class ChatamanServiceTest extends TestCase
{
    public function test_it_sends_the_approved_otp_template(): void
    {
        config()->set('services.chataman', [
            'base_url' => 'https://chataman.example',
            'token' => 'test-token',
            'token_header' => 'Authorization',
            'token_prefix' => 'Bearer',
            'timeout' => 15,
            'otp_template_name' => 'otp_el7lmplatform',
            'otp_template_language' => 'ar',
        ]);

        Http::fake([
            'chataman.example/api/send/template' => Http::response([
                'statusCode' => 200,
                'data' => [
                    'success' => true,
                    'data' => [
                        'success' => true,
                        'reached_meta' => true,
                        'messages' => [['id' => 'wamid.test']],
                        'chat' => [['value' => ['status' => 'sent']]],
                    ],
                ],
            ]),
        ]);

        (new ChatamanService())->sendOtp('+201070809633', '12345');

        Http::assertSent(fn ($request) =>
            $request->url() === 'https://chataman.example/api/send/template'
            && $request->hasHeader('Authorization', 'Bearer test-token')
            && $request['phone'] === '+201070809633'
            && $request['template']['name'] === 'otp_el7lmplatform'
            && $request['template']['language']['code'] === 'ar'
            && $request['template']['components'][0]['parameters'][0]['text'] === '12345'
            && $request['template']['components'][1]['parameters'][0]['text'] === '12345'
        );
    }

    public function test_it_throws_when_chataman_rejects_the_request(): void
    {
        config()->set('services.chataman', [
            'base_url' => 'https://chataman.example',
            'token' => 'test-token',
            'token_header' => 'Authorization',
            'token_prefix' => 'Bearer',
            'timeout' => 15,
            'otp_template_name' => 'otp_el7lmplatform',
            'otp_template_language' => 'ar',
        ]);

        Http::fake([
            'chataman.example/api/send/template' => Http::response(['message' => 'Unauthorized'], 401),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('status 401');

        (new ChatamanService())->sendOtp('+201070809633', '12345');
    }

    public function test_it_throws_when_chataman_does_not_reach_meta(): void
    {
        config()->set('services.chataman', [
            'base_url' => 'https://chataman.example',
            'token' => 'test-token',
            'token_header' => 'Authorization',
            'token_prefix' => 'Bearer',
            'timeout' => 15,
            'otp_template_name' => 'otp_el7lmplatform',
            'otp_template_language' => 'ar',
        ]);

        Http::fake([
            'chataman.example/api/send/template' => Http::response([
                'statusCode' => 200,
                'data' => [
                    'success' => true,
                    'data' => [
                        'success' => true,
                        'reached_meta' => false,
                    ],
                ],
            ]),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('did not confirm');

        (new ChatamanService())->sendOtp('+201070809633', '12345');
    }
}
