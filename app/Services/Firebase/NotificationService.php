<?php

namespace App\Services\Firebase;


use App\Models\Notification;
use Illuminate\Support\Str;

class NotificationService
{
    public static function firebaseNotification($notificationData, $token)
    {
        $SERVER_API_KEY = '';

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
        curl_exec($ch);

        return true;
    }

    public static function dbNotification($sender, $senderType, $type, $title, $body)
    {

        Notification::create([
            'id'=>Str::uuid(),
            'notifiable_id' => $sender,
            'notifiable_type' => $senderType,
            'type' =>$type,
            'title' => $title,
            'data' => $body,
        ]);

    }

}
