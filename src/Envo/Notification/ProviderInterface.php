<?php

namespace Envo\Notification;

interface ProviderInterface
{
    public function send(Notification $notification);

    public function validate();
}