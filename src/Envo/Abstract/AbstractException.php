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
    public $isJson = false;
    public $request;
	
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
        
        $this->request = $_REQUEST ?? null;
		
        parent::__construct($messageCode, $code, $previous);
    }
	
	/**
	 * @param $data
	 */
    public function setData($data, $public = false)
    {
    	$attribute = $public ? 'data' : 'internalData';
    	
        if( $data instanceof AbstractModel ) {
			$messages = $data->getMessages() ?: [];
			
            foreach ($messages as $message) {
                $this->$attribute[] = [
                    'field' => $message->getField(),
                    'type' => $message->getType(),
					'message' => $message->getMessage(),
                    // 'request' => isset($_REQUEST) ? $_REQUEST : null
                ];
            }
        }
        else if( $data instanceof Validator ) {
            $this->data = $data->errors();
        }
        else if( $data instanceof Exception ) {
            $this->$attribute = [
                'message' => $data->getMessage(),
                'code' => $data->getCode(),
                // 'request' => isset($_REQUEST) ? $_REQUEST : null
            ];
        }
        else {
            $this->$attribute = $data;
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
        
        try {
            $user = user();
        } catch(\Exception $exception) {
            $user = null;
        }

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
        $messagePublic = $this->messageCode;

        try {
            if(strpos($this->getMessage(), '.') !== false) {
                $message = \_t($this->getMessage());
            }
        } catch(\Exception $exception) {
            die(var_dump('AbstractException::json', $exception));
        }

        // die(var_dump($this->getMessage(), $code));

        $response = [
            'message' => $message,
            'success' => false,
            'data' => $this->data,
            'reference' => $this->reference,
            'code' => $this->messageCode,
        ];
        
        //if( env('APP_DEBUG') || ($user && $user->loggedIn && $user->isAdmin()) ) {
            $response['internal'] = [
                // 'message' => $this->getMessage(),
                'data' => $this->getInternalData(),
                'code' => $this->messageCode,
				'line' => $this->getLine(),
				'file' => $this->getFile(),
            ];

            if( $this->trace ) {
            	$previous = $this->getPrevious() ?: $this;
                $response['internal']['trace'] = $previous->getTraceAsString();
            }
        //}

        return $response;
    }

}