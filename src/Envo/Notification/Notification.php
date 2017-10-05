<?php

namespace Envo\Notification;

use Envo\AbstractEvent;

class Notification
{
    const PUSHOVER = 'Pushover';
    const SMS = 'Sms';
    const MAIL = 'Mail';

    public $body;
    public $subject;
    public $recipients;
    public $from;
    public $cc;
    public $bcc;
    public $providers;

    public function __construct($providers = null)
    {
        if( $this->providers !== null ) {
            // $this->providers = $providers;
        }
    }
    
    public function setProvider() {}
    public function setBody() {}
    public function setSubject() {}
    public function setRecipients() {}
    public function setFrom() {}

    public function setBCC() {}
    public function setCC() {}

    final public function send()
    {
        $handler = new Handler($this);
        $handler->send();
    }

    final public function queue($seconds = 60)
    {
        $handler = new Handler($this);
        $handler->queue($seconds);
    }

    public function getBody()
    {
        return $this->body;
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function getFrom()
    {
        return $this->from;
    }

    public function getRecipients()
    {
        return $this->recipients;
    }

    public function getBcc()
    {
        return $this->bcc;
    }

    public function getProviders()
    {
        return $this->providers;
    }
}