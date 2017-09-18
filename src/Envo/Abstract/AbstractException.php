<?php

namespace Envo;

use Envo\Support\Str;
use Envo\Support\Validator;

use Envo\Exception\PublicException;
use Envo\Exception\InternalException;

use Exception;

class AbstractException extends Exception
{
    public $reference = null;
    public $data = null;
    protected $internalData = [];
    public $messageCode = null;
    public $exception = [];
    public $trace = false;

    public function __construct($messageCode = null, $code = 0, Exception $previous = null)
    {
        $this->reference = Str::quickRandom() . '.' . time();
        $this->messageCode = $messageCode;
        return parent::__construct(\_t($messageCode), $code, $previous);
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
        else if( $data instanceof Exception ) {
            $this->internalData =[
                'message' => $data->getMessage(),
                'code' => $data->getCode(),
            ];
        }
        else {
            $this->internalData = $data;
        }
    }

    public function getInternalData()
    {
        return $this->internalData;
    }

    public function json()
    {
        $publicException = ($this instanceof PublicException);
        $code = $this->getCode();
        $user = user();

        $messages = [
            404 => 'api.notFound'
        ];

        $message = isset($messages[$code]) ? $messages[$code] : 'api.somethingWentWrong';

        $response = [
            'message' => $publicException ? $this->getMessage() : \_t($message),
            'success' => false,
            'data' => $this->data,
            'reference' => $this->reference,
            'code' => ! $publicException ? $message : $this->messageCode
        ];

        if( env('APP_DEBUG') || ($user && $user->loggedIn && $user->isAdmin()) ) {
            $response['internal'] = [
                // 'message' => $this->getMessage(),
                'data' => $this->getInternalData(),
                'code' => $this->messageCode,
            ];

            if( $this->trace ) {
                $response['internal']['trace'] = $this->getTrace();
            }
        }

        return $response;
    }

}