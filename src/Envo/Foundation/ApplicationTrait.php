<?php

namespace Envo\Foundation;

use Phalcon\Config\Adapter\Ini;

use Envo\Support\IP;

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

        ini_set('error_log', APP_PATH . 'storage/frameworks/logs/errors/'.date('Y-m.W').'.log');

		(new IP())->isBlocked();
    }

	public function setupConfig()
	{
		// $config = new Ini(APP_PATH . '.env');
		$config = parse_ini_file(APP_PATH . '.env');
		
		if( getenv('APP_ENV') == 'testing' ) {
			unset($config['APP_ENV']);
		}
		
		foreach($config as $key => $conf) {
			if( is_array($conf) ) {
				continue;
			}
			// var_dump($key, $conf);
			putenv($key.'='.$conf);
		}
	}
}