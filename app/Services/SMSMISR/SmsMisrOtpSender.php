<?php

namespace App\Services\SMSMISR;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class SmsMisrOtpSender
{
    private $client;
    private $environment;
    private $username;
    private $password;
    private $sender;
    private $apiUrl;
    private $templateToken;

    public function __construct()
    {
        $this->client = new Client();
        $this->environment = config('services.sms.environment');
        $this->username = config('services.sms.username');
        $this->password = config('services.sms.password');
        $this->sender = config('services.sms.sender');
        $this->apiUrl = config('services.sms.base_url_otp');
        $this->templateToken = config('services.sms.template_token');
    }

    public function sendOtp($mobile, $otp)
    {
        try {
            $response = $this->client->request('POST', $this->apiUrl, [
                'form_params' => [
                    'environment' => $this->environment,
                    'username' => $this->username,
                    'password' => $this->password,
                    'sender' => $this->sender,
                    'mobile' => $mobile,
                    'template' => $this->templateToken,
                    'otp' => $otp,
                ]
            ]);

            $body = json_decode($response->getBody()->getContents(), true);
            $body['message'] = $this->getMessageForCode($body['Code']);

            return [
                'code' => $body['Code'],
                'message' => $body['message'],
            ];
        } catch (GuzzleException $e) {
            return [
                'code' => 'error',
                'message' => 'Communication error: ' . $e->getMessage(),
            ];
        }
    }

    private function getMessageForCode($code): string
    {
        return match ($code) {
            '4901' => 'Success, Message Submitted Successfully',
            '4903' => 'Invalid value in username or password field',
            '4904' => 'Invalid value in "sender" field',
            '4905' => 'Invalid value in "mobile" field',
            '4906' => 'Insufficient Credit',
            '4907' => 'Server under updating',
            '4908' => 'Invalid OTP',
            '4909' => 'Invalid Template Token',
            '4912' => 'Invalid Environment',
            default => 'Unknown error',
        };
    }

}
