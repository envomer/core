<?php

namespace Envo;

use Envo\Support\Str;
use Envo\Support\Validator;

use Exception;

class AbstractException extends Exception
{
    public $reference = null;
    public $data = null;
    protected $internalData = [];
    public $messageCode = null;
    public $exception = [];

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
    }

    public function getInternalData()
    {
        return $this->internalData;
    }

    public function json()
    {
        $publicException = ($this instanceof PublicException);
        $code = $this->getCode();

        $response = [
            'message' => $publicException ? $this->getMessage() : \_t('api.somethingWentWrong'),
            'success' => false,
            'data' => $this->data,
            'reference' => $this->reference,
            'code' => ! $publicException ? 'api.somethingWentWrong' : $this->messageCode
        ];

        if( env('APP_DEBUG') || ($loggedIn && $this->getUser()->isAdmin()) ) {
            $response['internal'] = [
                'message' => $this->getMessage(),
                'data' => $this->getInternalData(),
                'code' => $this->messageCode,
                'trace' => $this->getTrace()
            ];
        }

        return $response;
    }

}