<?php

namespace Envo\Notification\Provider;

use Envo\Notification\Notification;
use Envo\Notification\ProviderInterface;

class Slack implements ProviderInterface
{
    protected $webhookUrl;

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
        $channel = '#server';
        $message = '@om. this tool is working good';

        $ch = curl_init("https://slack.com/api/chat.postMessage");
        $data = http_build_query([
            // "token" => 'xoxp-252980242100-252924753042-254027159015-1bf936028690a27cd5a18275577417dd',
            // "token" => 'xoxb-254030406039-nNtjy5cAlmmhVjKjEhFDTfku',
            "token" => 'xoxb-253000670772-lrn8gD9lzEWJ18Ze6XgGZaWf',
            "channel" => $channel, //"#mychannel",
            "text" => $message, //"Hello, Foo-Bar channel message.",
            "username" => "envobot",
            // 'as_user' => true
        ]);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
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
        $this->webhookUrl = env('SLACK_WEBHOOK_URL');

        if( ! $this->webhookUrl ) {
            internal_exception('notification.slackWebHookUrlNotGiven', 500);
        }

        return true;
    }
}