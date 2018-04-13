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

		if( $exception->getCode() === 404 ) {
			$code = 404;
			$message = null;
		}

		if( method_exists($exception, 'json') ) {
			$message = json_encode($exception->json());
		}

		$requestMethod = null;
		if( isset($_SERVER['REQUEST_METHOD']) ) {
			$requestMethod = $_SERVER['REQUEST_METHOD'] . ' ' . $_SERVER['REQUEST_URI'];
		}
		
		/**
		 * 2002: Means database is down. so don't record event in database
		 * 1049: Means database couldn't be found
		 */
		if( config('app.events.enabled', false) && ! is_a($e, 'PDOException') &&  $e->getCode() != 2002 && $e->getCode() != 1049 && ! env('APP_TESTING') ) {
			$event = new \Envo\Event\Exception($code .' '. $requestMethod. ' ', false, null, $message);

			if( $exception instanceof AbstractException ) {
				$event->getEvent()->reference = $exception->reference;
			}
			
			$event->save();
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
		$error = self::handleError($exception);
		envo_exception_handler($exception);
	}

	/**
	 * Handle the exception from any other place in the app
	 */
	public static function handleException($exception)
	{
		if( defined('APP_CLI') && APP_CLI ) {
			throw $exception;
		}

		$error = self::handleError($exception);
		envo_exception_handler($exception);
	}
}