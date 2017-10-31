<?php

namespace Envo\Notification;

class Notification
{
    const PUSHOVER = 'Pushover';
    const SMS = 'Sms';
    const MAIL = 'Mail';
    const SLACK = 'Slack';
	
	/**
	 * @var string
	 */
    public $body;
	
	/**
	 * @var string
	 */
    public $subject;
	
	/**
	 * @var string|array
	 */
    public $recipients;
	
	/**
	 * @var string
	 */
    public $from;
	
	/**
	 * @var string
	 */
    public $cc;
	
	/**
	 * @var string
	 */
    public $bcc;
	
	/**
	 * @see constants
	 * @var array|string
	 */
    public $providers;
	
	/**
	 * @TODO not sure about the array...maybe DTO?
	 *
	 * @var array
	 */
    public $url; // must be array [url, title]
	
	/**
	 * Notification constructor.
	 *
	 * @param string|array $providers
	 */
    public function __construct($providers = null)
    {
        if( $providers !== null ) {
            $this->providers = $providers;
        }
    }
	
	/**
	 * Set provider
	 *
	 * @param $providers
	 */
    public function setProviders($providers)
	{
		$this->providers = $providers;
	}
	
    public function setBody($body)
	{
		$this->body = $body;
	}
	
    public function setSubject($subject)
	{
		$this->subject = $subject;
	}
	
    public function setRecipients($recipients)
	{
		$this->recipients = $recipients;
	}
	
    public function setFrom($from)
	{
		$this->from = $from;
	}

    public function setBCC($bcc)
	{
		$this->bcc = $bcc;
	}
	
    public function setCC($cc)
	{
		$this->cc = $cc;
	}

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
	
	/**
	 * Get recipients
	 *
	 * @return string
	 */
    public function getRecipients()
    {
        return $this->recipients;
    }
	
	/**
	 * Get BCC
	 *
	 * @return string
	 */
    public function getBcc()
    {
        return $this->bcc;
    }
	
	/**
	 * Get providers
	 *
	 * @return array|null|string
	 */
    public function getProviders()
    {
        return $this->providers;
    }
}