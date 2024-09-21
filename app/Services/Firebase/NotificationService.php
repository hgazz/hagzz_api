<?php

namespace App\Services\Firebase;

use Google\Client as GoogleClient;
use App\Models\Notification;
use Illuminate\Support\Str;

class NotificationService
{
    public static function firebaseNotification($notificationData, $token)
    {
        $data = [
            "message" => [
                "token" => $token,
                "notification" => $notificationData,
            ]
        ];

        $credentialsFilePath = asset('bokit-eafed-firebase-adminsdk-n2vgb-1cdfccf166.json');
        $client = new GoogleClient();
        $client->setAuthConfig($credentialsFilePath);
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        $client->refreshTokenWithAssertion();
        $token = $client->getAccessToken();

        $access_token = $token['access_token'];

        $headers = [
            "Authorization: Bearer $access_token",
            'Content-Type: application/json'
        ];

        $dataString = json_encode($data);
        $ch = curl_init();
        $projectId = 'bokit-eafed';
        curl_setopt($ch, CURLOPT_URL, "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_exec($ch);

        if ($response === false || $httpCode !== 200) {
            dd($response);
            // Log the error or return an error response
            return false;
        }

        return true;
    }

    public static function dbNotification($sender, $senderType, $type, $title, $body, $image = null, $details = null)
    {

        Notification::create([
            'id'=>Str::uuid(),
            'notifiable_id' => $sender,
            'notifiable_type' => $senderType,
            'type' =>$type,
            'title' => $title,
            'description' => $body,
            'image' => $image,
            'details' => json_encode($details)
        ]);

    }

}
