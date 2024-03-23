<?php

namespace App\Services\SMSMISR;

use Illuminate\Support\Facades\Http;

class SmsService
{
    protected $baseUrl;
    protected $environment = 1; // 2=>test environment
    protected $username = 'YourUsername';
    protected $password = 'YourPassword';
    protected $sender = 'b611afb996655a94c8e942a823f1421de42bf8335d24ba1f84c437b2ab11ca27';
    protected $language = 1; // English

    public function __construct()
    {
        $this->baseUrl = 'https://smsmisr.com/api/SMS/';
        $this->username = config('services.sms.username');
        $this->password = config('services.sms.password');
        $this->sender = config('services.sms.sender');
    }
    public function sendMessage($mobile, $message)
    {
        $response = Http::post($this->baseUrl, [
            'environment' => $this->environment,
            'username' => $this->username,
            'password' => $this->password,
            'sender' => $this->sender,
            'mobile' => $mobile,
            'language' => $this->language,
            'message' => $message,
        ]);

        $responseBody = $response->json();

        return $this->interpretResponse($responseBody);
    }

    protected function interpretResponse($response)
    {
        $errorCodes = [
            '1901' => [
                'status' => 'success',
                'message' => 'Message Submitted Successfully',
                'SMSID' => $response['SMSID'] ?? null
            ],
            '1902' => ['message' => 'Invalid Request'],
            '1903' => ['message' => 'Invalid value in username or password field'],
            '1904' => ['message' => 'Invalid value in "sender" field'],
            '1905' => ['message' => 'Invalid value in "mobile" field'],
            '1906' => ['message' => 'Insufficient Credit'],
            '1907' => ['message' => 'Server under updating'],
            '1908' => ['message' => 'Invalid Date & Time format in “DelayUntil=” parameter'],
            '1909' => ['message' => 'Invalid Message'],
            '1910' => ['message' => 'Invalid Language'],
            '1911' => ['message' => 'Text is too long'],
            '1912' => ['message' => 'Invalid Environment']
        ];

        return $errorCodes[$response['code']] ?? ['status' => 'error', 'message' => 'Unknown error occurred'];
    }
}
