<?php

namespace App\Services\Firebase;


use App\Models\Notification;
use Illuminate\Support\Str;

class NotificationService
{
    public static function firebaseNotification($notificationData, $token)
    {
        $SERVER_API_KEY = 'AAAAU9DrP9s:APA91bHPZSjTIEgNGFTL28H-yMuNFdwe84zfe0zQ7m0epSK0y7UsmLkVVU-vP9jkkaOfPnRiS72OaCv0WscyDn85jiYGnHVd83AbYCENvGiKYFXXqPYzDTExc_-ZQkcLmbMMbJWYgXwA';

        $data = [
            'registration_ids' => [
                $token,
            ],
            'notification' => $notificationData,
        ];

        $dataString = json_encode($data);

        $headers = [
            'Authorization: key='.$SERVER_API_KEY,
            'Content-Type: application/json',
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
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
