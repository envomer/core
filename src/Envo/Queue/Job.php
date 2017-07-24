<?php

namespace Envo\Queue;

use Envo\AbstractDTO;

class Job extends AbstractDTO
{
    const STATUS_FAILED = -1;
    const STATUS_OK = 1;

    public $queue;
    public $id = null;
    public $type_id;
    public $payload;
    public $attempts = 1;
    public $reserved_at;
    public $available_at;
    public $created_at;
    public $exception;
    public $failed_at = null;
    public $status = null;
    public $done = 0;

    public $entity = null;
}