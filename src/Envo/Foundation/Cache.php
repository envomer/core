<?php

namespace Envo\Foundation;

use Phalcon\Cache\Backend\File as BackFile;
use Phalcon\Cache\Frontend\Data as FrontData;

class Cache
{
	protected static $cache = null;

	public static function getInstance()
	{
		if( self::$cache ) {
            return self::$cache;
        }

		$frontCache = new FrontData(
		    array(
                /** TODO add to config file **/
		        "lifetime" => 172800 // 2 days 
		    )
		);

		$cache = new BackFile(
		    $frontCache,
		    array(
		    	'prefix' => 'cache_',
		        "cacheDir" => APP_PATH . 'storage/framework/cache/'
		    )
		);

		return self::$cache = $cache;
	}

	public static function delete($key)
	{
		$cache = self::getInstance();
		return $cache->delete($key);
	}
}