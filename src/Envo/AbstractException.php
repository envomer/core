<?php

namespace Envo;

use Envo\Support\Str;

class AbstractException extends \Exception
{
    public $reference = null;

    public function __construct($message = null, $code = 0, Exception $previous = null)
    {
        $this->reference = Str::quickRandom() . '.' . time();
        return parent::__construct($message, $code, $previous);
    }
}