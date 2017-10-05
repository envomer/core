<?php

namespace Envo\Notification\Provider;

use Envo\Notification\Notification;
use Envo\Notification\ProviderInterface;

class Pushover implements ProviderInterface
{
    protected $token;
    
    protected $user;

    public function __construct()
    {
        $this->validate();
    }

    /**
     * Send notification
     *
     * @param Notification $notification
     * @return void
     */
    public function send(Notification $notification)
    {
        curl_setopt_array($ch = curl_init(), array(
            CURLOPT_URL => "https://api.pushover.net/1/messages.json",
            CURLOPT_POSTFIELDS => array(
                'token' => $this->token,
                'user' => $this->user,
                'message' => $notification->getBody(),
                'title' => $notification->getSubject()
            ),
            CURLOPT_SAFE_UPLOAD => true,
            CURLOPT_RETURNTRANSFER => true,
        ));
        $response = curl_exec($ch);
        curl_close($ch);

        die(var_dump($ch, $response));

        // TODO: return response..
    }

    /**
     * Validate pushover service
     *
     * @return bool
     */
    public function validate()
    {
        $this->user = env('PUSHOVER_USER');
        $this->token = env('PUSHOVER_TOKEN');

        if( ! $this->user ) {
            internal_exception('notification.pushoverUserNotGiven', 500);
        }

        if( ! $this->token ) {
            internal_exception('notification.pushoverTokenNotGiven', 500);
        }
        
        return true;
    }
}