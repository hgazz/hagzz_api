<?php

namespace App\Services\Beon;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

class BeonService
{
    protected $client;
    protected $token;
    protected $baseUri;

    public function __construct()
    {
        $this->client = new Client();
        $this->token = 'vSCuMzZwLjDxzR882YphwEgW';
        $this->baseUri = 'https://beon-system.unlimited-software.com/api/send/message/otp';
    }

    public function sendOtp($phoneNumber, $name)
    {
        $headers = [
            'beon_token' => $this->token,
        ];

        $options = [
            'multipart' => [
                [
                    'name' => 'phoneNumber',
                    'contents' => $phoneNumber,
                ],
                [
                    'name' => 'name',
                    'contents' => $name,
                ],
            ],
        ];

        $request = new Request('POST', $this->baseUri, $headers);
        $response = $this->client->sendAsync($request, $options)->wait();

        return $response->getBody()->getContents();
    }
}


