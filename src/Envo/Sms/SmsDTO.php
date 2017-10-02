<?php

namespace Envo\Sms;

use Envo\AbstractDTO;

class SmsDTO extends AbstractDTO
{
    /**
     * Recipients
     *
     * @var string|array
     */
    public $recipients = [];

    public $from;

    public $body;

    public $teamId;

    public $userId;
}