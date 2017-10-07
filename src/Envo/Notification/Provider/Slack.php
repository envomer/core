<?php

namespace Envo\Notification\Provider;

use Envo\Notification\Notification;
use Envo\Notification\ProviderInterface;

class Slack implements ProviderInterface
{
    protected $webhookUrl;

    public $icon = ':smirk:';

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
     * Guide: https://api.slack.com/incoming-webhooks
     *
     * @param Notification $notification
     * @return void
     */
    public function send(Notification $notification)
    {
        $ch = curl_init($this->webhookUrl);
        $data = json_encode([
            'text' => $notification->getBody(),
            'username' => 'serverbot',
            'icon_emoji' => $this->icon,
            // 'channel' => $notification->
        ]);
        
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        die(var_dump($response));
    }

    /**
     * Validate slack service
     *
     * @return bool
     */
    public function validate()
    {
        $this->webhookUrl = env('SLACK_WEBHOOK_URL');

        if( ! $this->webhookUrl ) {
            internal_exception('notification.slackWebHookUrlNotGiven', 500);
        }

        return true;
    }
}