<?php

namespace App\Services\Firebase;

use Illuminate\Support\Facades\Notification;

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

    public static function dbNotification($data, $sender, $receiver, $senderType)
    {

        Notification::create([
            'sender_id' => $sender,
            'receiver_id' => $receiver,
            'sender_type' => $senderType,
            'title' => $data['title'],
            'message' => $data['message'],
        ]);

    }

}
