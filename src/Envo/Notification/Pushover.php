<?php

namespace Envo\Notification;

class Pushover
{
    public function send($message)
    {
        curl_setopt_array($ch = curl_init(), array(
            CURLOPT_URL => "https://api.pushover.net/1/messages.json",
            CURLOPT_POSTFIELDS => array(
                'token' => env('PUSHOVER_TOKEN'),
                'user' => env('PUSHOVER_USER'),
                'message' => $message,
            ),
            CURLOPT_SAFE_UPLOAD => true,
            CURLOPT_RETURNTRANSFER => true,
        ));
        curl_exec($ch);
        curl_close($ch);

        // TODO: return response..
    }
}