<?php

namespace Envo\Foundation;

use Exception;

use Envo\AbstractException;
use Envo\AbstractEvent;

use Phalcon\Events\Event;
use Phalcon\Mvc\User\Plugin;
use Phalcon\Dispatcher;
use Phalcon\Mvc\Dispatcher as MvcDispatcher;

/**
 * ExceptionHandler
 *
 * Handles not-found controller/actions
 */
class ExceptionHandler
{
	public static function handle(Exception $exception)
	{
		$trace = true;
		$exceptionActual = $exception;
		
		if ( $exception instanceof PublicException ) {
			http_response_code($exception->getCode());
			// $trace = false;
		} else {
			http_response_code(500);
		}


		// die(var_dump($exception->getMessage()));
		
		// hmmm
		if(!($exception instanceof AbstractException) && class_exists(\Envo\Exception\InternalException::class)) {
			$isJson = property_exists($exception, 'isJson');
			$exception = new \Envo\Exception\InternalException($exception->getMessage(), is_numeric($exception->getCode()) ? $exception->getCode() : null, $exception instanceof \Exception ? $exception : null);
			if($isJson) {
				$exception->isJson = true;
			}
		}

		
		//TODO: sure about this??
		// TODO: catch offline database exception?
		try {
			//die(var_dump('here?'));
			if ( $exception instanceof AbstractException ) {
				if($trace) {
					$exception->trace = true;
				}
				$json = $exception->json();
				//die(var_dump('here?', $trace));
				$dataEvent = $json;
				if( $_REQUEST !== null ) {
					$dataEvent['request'] = $_REQUEST;
					$dataEvent['uri'] = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
				}
				new \Envo\Event\Exception($exception->getMessage(), true, null, $dataEvent);
				if ( $exception->isJson || (($router = resolve('router')) && ($route = $router->getMatchedRoute()) && strpos($route->getPattern(), '/api/') === 0 )) {
					header('Content-Type: application/json');
					echo json_encode($json);
					exit;
				}
				
			} else if(class_exists(\Envo\Event\Exception::class)) {
				//die(var_dump('here?'));
				new \Envo\Event\Exception($exception->getMessage(), true, null, [
					'request' => isset($_REQUEST) ? $_REQUEST : '',
					'uri' => isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '',
					'trace_string' => $exceptionActual->getTraceAsString(),
					'trace' => $exceptionActual->getTrace()
				]);
			}
		} catch (\Exception $e) {
			// die(var_dump($e));
		}
		
		if(defined('APP_CLI') && APP_CLI) {
			die(var_dump($exception->getMessage()));
		}
		
		$error = $exception;
		//require_once __DIR__ . '/View/html/errors.php';
		require_once ENVO_PATH . 'Envo/View/html/errors.php';
		exit;
	}
	
	/**
	 * Handle all exceptions types here
	 */
	public static function handleError(Exception $exception)
	{
		$e = $exception;
		$code = $exception->getCode();

		$message = $e->getMessage(). "\n"
			 . ' Class:' . get_class($e) . "\n"
             . ' File:' . $e->getFile(). "\n"
             . ' Date:' . date('Y-m-d H:i:s') . "\n"
             . ' Line:' . $e->getLine(). "\n";

		if( method_exists($exception, 'json') ) {
			$message = json_encode($exception->json());
		}

		$requestMethod = null;
		if( isset($_SERVER['REQUEST_METHOD']) ) {
			$requestMethod = $_SERVER['REQUEST_METHOD'] . ' ' . $_SERVER['REQUEST_URI'];
		}

		if( $exception->getCode() === 404 ) {
			$code = 404;
			$message = $requestMethod;
		}
		
		/**
		 * 2002: Means database is down. so don't record event in database
		 * 1049: Means database couldn't be found
		 */
		if( config('app.events.enabled', false) && ! is_a($e, 'PDOException') &&  $e->getCode() !== 2002 && $e->getCode() !== 1049 && ! env('APP_TESTING') ) {
			// $event = new \Envo\Event\Exception($code .' '. $requestMethod. ' ', false, null, $message);

			// if( $exception instanceof AbstractException ) {
			// 	$event->getEvent()->reference = $exception->reference;
			// }
			
			// $event->save();
			// $event->notify();
		}

		// send a Notification every 5 minutes if the error repeats itself
		if( env('APP_ENV') === 'production' ) {
			// \Notification::pushoverRemind($_SERVER['SERVER_NAME'],  'IP: ' . \IP::getIpAddress() . "\nCode: ".  $code . ' ' . $requestMethod . ' ' . "\n\rMessage: " . $message, 60*5);
		}

		// also log the error message into a log file
       	error_log('['. date('Y-m-d H:i:s'). '] ' . $code . ' ' .$message . "\n" . $e->getTraceAsString() . "\n", 3, APP_PATH . 'storage/framework/logs/errors/'.date('Y-m.W') . '.log');
	}

	/**
	 * This action is executed before any exception occurs when running phalcon
	 *
	 * @param Event $event
	 * @param MvcDispatcher $dispatcher
	 * @param Exception $exception
	 * @return boolean
	 */
	public function beforeException(Event $event, MvcDispatcher $dispatcher, Exception $exception)
	{
		$source = $event ? $event->getSource() : null;
		if($source) {
			$controller = $source ? $source->getActiveController() : null;
			$action = $source ? $source->getActionName() : null;

			// Catch all xhr controller exceptions and return exception as json objects
			if(($action && substr($action, 0, 3) === 'xhr') || ($controller && method_exists($controller, 'isXhr') && $controller->isXhr())) {
				$exception->isJson = true;
			}
		}
		
		//die(var_dump($exception->getTraceAsString()));
		self::handleError($exception);
		//envo_exception_handler($exception);
		
		self::handle($exception);
		
		//$error = $exception;
		//require_once ENVO_PATH . 'Envo/View/html/errors.php';
	}

	/**
	 * Handle the exception from any other place in the app
	 */
	public static function handleException($exception)
	{
		if( defined('APP_CLI') && APP_CLI ) {
			throw $exception;
		}
		//die(var_dump($exception->getTraceAsString()));

		$error = self::handleError($exception);
		envo_exception_handler($exception);
	}
}