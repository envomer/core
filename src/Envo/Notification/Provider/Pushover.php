<?php

namespace Envo\Notification\Provider;

use Envo\Notification\Notification;
use Envo\Notification\ProviderInterface;

class Pushover implements ProviderInterface
{
    protected $token;
    
    protected $user;

    public $url;

    public $urlTitle;

    public $priority;

    /**
     * Sound
     * 
     * Possible values:
     * pushover, bike, bugle, cashregister, classical, cosmic, falling, gamelan
     * incoming, intermission, magic, mechanical, pianobar, siren, spacealarm, tugboat
     * alien, climb, persistent, echo, updown, none
     *
     * @var string
     */
    public $sound = 'pushover';

    /**
     * Pushover construct
     */
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
                'title' => $notification->getSubject(),
                'priority' => $this->priority,
                'url' => $this->url,
                'url_title' => $this->urlTitle,
                'sound' => $this->sound
            ),
            CURLOPT_SAFE_UPLOAD => true,
            CURLOPT_RETURNTRANSFER => true,
        ));
        $response = curl_exec($ch);
        curl_close($ch);

        die(var_dump($response));

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