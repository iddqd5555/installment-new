<?php

namespace App\Services;

class FcmService
{
    public static function send($to, $title, $body, $data = [])
    {
        $serverKey = config('services.fcm.server_key') ?: env('FCM_SERVER_KEY');
        if (!$serverKey || !$to) return false;

        $msg = [
            'to' => $to,
            'notification' => [
                'title' => $title,
                'body' => $body,
                'sound' => 'default',
            ],
            'data' => $data,
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: key=' . $serverKey,
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($msg));
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }
}
