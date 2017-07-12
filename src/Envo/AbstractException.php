<?php

namespace Envo;

use Envo\Support\Str;
use Envo\Support\Validator;

class AbstractException extends \Exception
{
    public $reference = null;
    public $data = null;
    protected $internalData = [];

    public function __construct($message = null, $code = 0, Exception $previous = null)
    {
        $this->reference = Str::quickRandom() . '.' . time();
        return parent::__construct($message, $code, $previous);
    }

    public function setData($data)
    {
        if( $data instanceof AbstractModel && ($messages = $data->getMessages()) ) {
            foreach ($messages as $message) {
                $this->internalData[] = [
                    'field' => $message->getField(),
                    'type' => $message->getType()
                ];
            }
        }
        else if( $data instanceof Validator ) {
            $this->data = $data->errors();
        }
    }

    public function getInternalData()
    {
        return $this->internalData;
    }
}