<?php

namespace Envo\Notification;

class Handler
{
    protected $notification = null;
    protected $providers = [];

    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
    }

    /**
     * Send notification
     *
     * @return bool
     */
    public function send()
    {
        $this->validate();
        $this->setProviders();

        /**
         * ????
         * loop through providers and send one after another
         */

        foreach ($this->providers as $provider) {
            $provider->send($this->notification);
        }

        return true;
    }

    /**
     * Set providers of given notification instance
     *
     * @return void
     */
    public function setProviders()
    {
        $providers = $this->notification->providers;

        if( is_string($providers) ) {
            $providers = [$providers];
        }

        $instances = [];
        foreach($providers as $provider) {
            if( is_string($provider) ) {
                $providerName = '\Envo\Notification\Provider\\' . $provider;
                $provider = new $providerName();
            }
            $instances[] = $provider;
        }

        $this->providers = $instances;
    }

    /**
     * Validate given notification instance
     *
     * @return bool
     */
    public function validate()
    {
        $providers = $this->notification->getProviders();
        // die(var_dump($this->notification));
        if( ! $providers ) {
            internal_exception('notification.providersNotGiven', 500);
        }

        return true;
    }
}