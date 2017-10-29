<?php

namespace Envo\Foundation;

use Envo\Support\IP;

trait ApplicationTrait
{
	/**
	 * Define error logging and check if .env file exists
	 *
	 * @throws \Exception
	 */
    public function setup()
    {
        define('APP_START', microtime(true));
        define('ENVO_PATH', __DIR__ . '/../');

        if( ! defined('APP_PATH') ) {
            exit('APP_PATH not defined');
        }
	
		/**
		 * Read configuration file
		 */
		if(! file_exists(APP_PATH . '.env') ) {
			throw new \Exception('Configuration file not set. Contact support team.', 500);
		}

        ini_set('error_log', APP_PATH . 'storage/frameworks/logs/errors/'.date('Y-m.W').'.log');

		// IP check
		(new IP())->isBlocked();
    }
	
	/**
	 * Setup .env configuration
	 */
	public function setupConfig()
	{
		$config = parse_ini_file(APP_PATH . '.env');
		
		if( getenv('APP_ENV') === 'testing' ) {
			unset($config['APP_ENV']);
		}
		
		foreach($config as $key => $conf) {
			if( is_array($conf) ) {
				continue;
			}
			putenv($key.'='.$conf);
		}
	}
}