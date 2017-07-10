<?php

namespace Envo\Foundation;

trait ApplicationTrait
{
    public function setup()
    {
        define('APP_START', microtime(true));
        define('ENVO_PATH', __DIR__ . '/../');

        if( ! defined('APP_PATH') ) {
            exit('APP_PATH not defined');
        }

        /** Read the configuration */
		if( ! file_exists(APP_PATH . '.env') ) {
			throw new \Exception("Configuration file not set. Contact support team.", 500);
		}

        ini_set("error_log", APP_PATH . 'storage/logs/errors/'.date('Y-m-d').'.log');
    }

	public function setupConfig()
	{
		$config = new \Phalcon\Config\Adapter\Ini(APP_PATH . '.env');
		
		if( getenv('APP_ENV') == 'testing' ) {
			unset($config['APP_ENV']);
		}
		
		foreach($config as $key => $conf) {
			putenv($key.'='.$conf);
		}
	}
}