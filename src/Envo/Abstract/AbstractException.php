<?php

namespace Envo;

use Envo\Support\Str;
use Envo\Support\Validator;

use Envo\Exception\PublicException;
use Envo\Exception\InternalException;

use Exception;

class AbstractException extends Exception
{
    protected $internalData = [];
    public $reference;
    public $data;
    public $messageCode;
    public $exception = [];
    public $trace = false;
	
	/**
	 * AbstractException constructor.
	 *
	 * @param null           $messageCode
	 * @param int            $code
	 * @param Exception|null $previous
	 */
    public function __construct($messageCode = null, $code = 0, Exception $previous = null)
    {
        $this->reference = Str::quickRandom() . '.' . time();
        $this->messageCode = $messageCode;
		
        parent::__construct(\_t($messageCode), $code, $previous);
    }
	
	/**
	 * @param $data
	 */
    public function setData($data)
    {
        if( $data instanceof AbstractModel && ($messages = $data->getMessages()) ) {
            foreach ($messages as $message) {
                $this->internalData[] = [
                    'field' => $message->getField(),
                    'type' => $message->getType(),
					'message' => $message->getMessage()
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
	
	/**
	 * @return array
	 */
    public function getInternalData()
    {
        return $this->internalData;
    }
	
	/**
	 * @return array
	 */
    public function json()
    {
        $publicException = ($this instanceof PublicException);
        $code = $this->getCode();
        $user = user();

        $messages = [
            400 => 'api.badRequest',
            401 => 'api.unauthorized',
            403 => 'api.forbidden',
            404 => 'api.notFound',
            405 => 'api.methodNotAllowed',
            409 => 'api.conflict',
            500 => 'api.internalServerError',
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
				'line' => $this->getLine(),
				'file' => $this->getFile(),
            ];

            if( $this->trace ) {
                $response['internal']['trace'] = $this->getTrace();
            }
        }

        return $response;
    }

}