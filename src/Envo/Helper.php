<?php

use Envo\Support\Translator;
use Envo\Foundation\Config;

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
		if( $amount ) {
			return resolve(Translator::class)->choice($val, $amount, $lang);
		}
		return resolve(Translator::class)->lang($val, $params, $lang);
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
 * needed to fetch the headers when using api
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
				$rx_matches = array();
				// do some nasty string manipulations to restore the original letter case
				// this should work in most cases
				$rx_matches = explode('_', $arh_key);
				if( count($rx_matches) > 0 and strlen($arh_key) > 2 ) {
					foreach($rx_matches as $ak_key => $ak_val) $rx_matches[$ak_key] = ucfirst($ak_val);
					$arh_key = implode('-', $rx_matches);
				}
				$arh[$arh_key] = $val;
			}
		}
		return( $arh );
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
        if (is_null($key)) {
            return app('session');
        }

        if (is_array($key)) {
            return app('session')->put($key);
        }

        return app('session')->get($key, $default);
    }
}

/**
 * Get current user
 */
if( ! function_exists('user') )
{
	function user()
	{
		return \Auth::user();
	}
}

/**
 * Handle all exceptions
 * TODO: modify
 */
function envo_exception_handler($error)
{
	require_once ENVO_PATH . 'View/html/errors.php';
	exit;
}

/**
 * Abort
 */
if( ! function_exists('abort') )
{
	function abort($code = 403, $message = null)
	{
		\App::abort($code, $message);
	}
}

/**
 * Abort unless
 */
if( ! function_exists('abort_unless') )
{
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
	function resolve($class, $instance = null)
	{
		$di = \Phalcon\DI::getDefault();
		if( ! ($repo = $di->getShared($class)) ) {
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
	function config($name)
	{
		return resolve(Config::class)->get($name);
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