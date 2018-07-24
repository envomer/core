<?php

use Envo\AbstractException;
use Envo\Exception\PublicException;

/**
 * Environment helper function.
 * Retrieve data defined in .env file
 */
if( ! function_exists('env') )
{
	function env($name, $default = false)
	{
		return ($env = getenv($name)) !== false ? $env : $default;
	}
}

/**
 * Translation helper
 */
if( ! function_exists('_t') )
{
	function _t($val, $params = null, $amount = null, $lang = null)
	{
		$translator = resolve('translator');

		if(!$translator) {
			return $val;
		}

		if( $amount && ! is_bool($params) ) {
			return $translator->choice($val, $amount, $lang);
		}
		
		return $translator->lang($val, $params, $lang);
	}
}

/**
 * Get the render time
 */
if( ! function_exists('render_time') )
{
	function render_time()
	{
		return microtime(true) - APP_START;
	}
}

/**
 * Public path
 */
if( ! function_exists('public_path') )
{
	function public_path($path = '')
	{
        return '';
	}
}

/**
 * Needed to fetch the headers when using api
 */
if( ! function_exists('apache_request_headers') )
{
	function apache_request_headers()
	{
		$arh = array();
		$rx_http = '/\AHTTP_/';
		foreach($_SERVER as $key => $val) {
			if( preg_match($rx_http, $key) ) {
				$arh_key = preg_replace($rx_http, '', $key);
				// do some nasty string manipulations to restore the original letter case
				// this should work in most cases
				$rx_matches = explode('_', $arh_key);
				if( count($rx_matches) > 0 and strlen($arh_key) > 2 ) {
					foreach($rx_matches as $ak_key => $ak_val) {
						$rx_matches[ $ak_key ] = ucfirst($ak_val);
					}
					$arh_key = implode('-', $rx_matches);
				}
				$arh[$arh_key] = $val;
			}
		}
		return $arh;
	}
}

/**
 * Die and dump
 */
if( ! function_exists('dd') )
{
	function dd($arg)
	{
		die((new \Phalcon\Debug\Dump())->variables($args));
	}
}

/**
 * Return the default value of a given value.
 */
if (! function_exists('value')) {
    /**
     * Return the default value of the given value.
     *
     * @param  mixed  $value
     * @return mixed
     */
    function value($value)
    {
        return $value instanceof Closure ? $value() : $value;
    }
}

/**
 * Get / set the specified cache value.
 * 
 * set (['key' => 'value'], lifetime) //lifetime only works with memcache
 * get(key, lifetime, defaultvalue)
 *
 * If an array is passed, we'll assume you want to put to the cache.
 *
 * @param  dynamic  key|key,default|data,expiration|null
 * @return mixed
 *
 * @throws \Exception
 */
if (! function_exists('cache'))
{
    function cache()
    {
        $arguments = func_get_args();
        $instance = resolve(Cache::class);

        if (empty($arguments)) {
            return $instance;
        }

        if (is_string($arguments[0])) {
        	return $instance->get($arguments[0], (isset($arguments[1]) ? $arguments[1] : null)) ?: (isset($arguments[2]) ? $arguments[2] : null);
        }

        if (is_array($arguments[0])) {
            return $instance->save(key($arguments[0]), reset($arguments[0]), (isset($arguments[1]) ? $arguments[1] : null));
        }
    }
}

/**
 * Get / set the specified session value.
 *
 * If an array is passed as the key, we will assume you want to set an array of values.
 *
 * @param  array|string  $key
 * @param  mixed  $default
 * @return mixed
 */
if (! function_exists('session'))
{
    function session($key = null, $default = null)
    {
        if ( null === $key ) {
            return resolve('session');
        }

        if (is_array($key)) {
            return resolve('session')->put($key);
        }

        return resolve('session')->get($key, $default);
    }
}

/**
 * Get current user
 */
if( ! function_exists('user') )
{
	/**
	 * @return \Envo\Model\User
	 */
	function user()
	{
		return resolve('auth')->user();
	}
}

/**
 * Handle all exceptions
 * TODO: modify
 */
if(!function_exists('envo_exception_handler'))
{
	function envo_exception_handler($error)
	{
		$trace = true;
		if ( $error instanceof PublicException ) {
			http_response_code($error->getCode());
			// $trace = false;
		} else {
			http_response_code(500);
		}

		if(!($error instanceof AbstractException) && class_exists(\Envo\Exception\InternalException::class)) {
			$isJson = isset($error->isJson);
			$error = new \Envo\Exception\InternalException($error->getMessage(), $error->getCode(), $error instanceof \Exception ? $error : null);
			if($isJson) {
				$error->isJson = true;
			}
		}
		
		//TODO: sure about this??
		// TODO: catch offline database exception?
		try {
			if ( $error instanceof AbstractException ) {
				if($trace) {
					$error->trace = true;
				}
				$json = $error->json();
				new \Envo\Event\Exception($error->getMessage(), true, null, $json);
				if ( $error->isJson || (($router = resolve('router')) && ($route = $router->getMatchedRoute()) && strpos($route->getPattern(), '/api/') === 0 )) {
					header('Content-Type: application/json');
					echo json_encode($json);
					exit;
				}
				
			} else if(class_exists(\Envo\Event\Exception::class)) {
				new \Envo\Event\Exception($error->getMessage(), true, null, ['trace' => $error->getTraceAsString()]);
			}
		} catch (\Exception $e) {
			// die(var_dump($e));
		}

		if(defined('APP_CLI') && APP_CLI) {
			die(var_dump($error->getMessage()));
		}
		
		require_once __DIR__ . '/View/html/errors.php';
		exit;
	}
}

/**
 * Turn all errors into exceptions
 */
if(!function_exists('envo_error_handler'))
{
	function envo_error_handler($errno, $errstr, $errfile, $errline)
	{
		if (!(error_reporting() & $errno)) {
			// This error code is not included in error_reporting, so let it fall
			// through to the standard PHP error handler
			return false;
		}
		
		$error = new \ErrorException($errstr, 0, $errno, $errfile, $errline);
	
		envo_exception_handler($error);
	
		/* Don't execute PHP internal error handler */
		return true;
	}
}

/**
 * Abort unless
 */
if( ! function_exists('abort_unless') )
{
	/**
	 * @param      $condition
	 * @param int  $code
	 * @param null $message
	 */
	function abort_unless($condition, $code = 403, $message = null)
	{
		if( ! $condition ) {
			abort($code, $message);
		}
	}
}

/**
 * Resolve
 */
if( ! function_exists('resolve') )
{
	/**
	 * @param      $class
	 * @param null $instance
	 *
	 * @return mixed
	 */
	function resolve($class, $instance = null)
	{
		$di = \Phalcon\DI::getDefault();

		if(!$di) {
			return null;
		}

		if( $di && ! ($repo = $di->getShared($class)) ) {
			$repo = $instance ?: new $repo;
			$di->setShared($class, $repo);
		}

        return $repo;
	}
}

/**
 * Config
 */
if( ! function_exists('config') )
{
	function config($name = null, $default = null)
	{
		$config = resolve('config');
		if(!$name) {
			return $config;
		}
		return $config->get($name, $default);
	}
}

/**
 * Event listener
 */
if( ! function_exists('on') )
{
	function on($name, $callback)
	{
		return resolve('eventsManager')->attach($name, $callback);
	}
}

/**
 * Trigger event
 */
if( ! function_exists('fire') )
{
	function fire($name, $data)
	{
		return resolve('eventsManager')->fire($name, $data);
	}
}

/**
 * Trigger public exception
 */
if( ! function_exists('public_exception') )
{
	/**
	 * @param      $messageCode
	 * @param      $code
	 * @param null $data
	 *
	 * @throws PublicException
	 */
	function public_exception($messageCode, $code = 500, $data = null)
	{
		$exception = new \Envo\Exception\PublicException($messageCode, $code);
		$exception->setData($data);

		throw $exception;
	}
}

/**
 * Trigger private exception
 */
if( ! function_exists('internal_exception') )
{
	/**
	 * @param      $messageCode
	 * @param      $code
	 * @param null $data
	 *
	 * @throws \Envo\Exception\InternalException
	 */
	function internal_exception($messageCode, $code = 500, $data = null)
	{
		$exception = new \Envo\Exception\InternalException($messageCode, $code);
		$exception->setData($data);

		throw $exception;
	}
}

/**
 * Trigger public exception
 */
if( ! function_exists('uncaught_exception') )
{
	/**
	 * @param Exception $exception
	 * @param int       $code
	 *
	 * @return \Envo\Exception\InternalException|Exception
	 */
	function uncaught_exception(\Exception $exception, $code = 500)
	{
		if( $exception instanceof AbstractException ) {
			return $exception;
		}

		$internal = new \Envo\Exception\InternalException(
			'app.uncaughtException', $code, $exception
		);

		$internal->setData($exception);
		$internal->trace = true;

		return $internal;
	}
}

if( ! function_exists('encrypt') )
{
	/**
	 * @param Exception $exception
	 * @param int       $code
	 *
	 * @return \Envo\Exception\InternalException|Exception
	 */
	function encrypt($value = null, $key = null)
	{
		$crypt = resolve('crypt');

		if($value) {
			return $crypt->encrypt($value, $key);
		}

		return $crypt;
	}
}

if( ! function_exists('decrypt') )
{
	/**
	 * @param Exception $exception
	 * @param int       $code
	 *
	 * @return \Envo\Exception\InternalException|Exception
	 */
	function decrypt($value = null, $key = null)
	{
		$crypt = resolve('crypt');

		if($value) {
			return $crypt->decrypt($value, $key);
		}

		return $crypt;
	}
}


