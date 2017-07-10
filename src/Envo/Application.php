<?php

namespace Envo;

use Envo\Foundation\IP;

class Application extends \Phalcon\Mvc\Application
{
    public $inMaintenance = null;

    public function isMaintained($complete = false)
	{
		if( $this->inMaintenance === null ) {
			$this->inMaintenance = @file_get_contents(APP_PATH . 'storage/framework/down') ?: false;

			if( $this->inMaintenance ) {
				$this->inMaintenance = json_decode($this->inMaintenance);
			}
		}

		if( $this->inMaintenance ) {
			$maintenance = $this->inMaintenance;
			$maintenance->retry = $maintenance->retry ?: 60;
			$maintenance->progress = abs(floor(((($maintenance->time + $maintenance->retry) - time())/$maintenance->retry)));
			$maintenance->progress = $maintenance->progress >= 98 ? 98 : $maintenance->progress;
			require ENVO_PATH . 'View/html/maintenance.php';
			die;
		}
	}

    public function setup()
    {
        define('APP_START', microtime(true));
        define('ENVO_PATH', __DIR__ . '/');

        if( ! defined('APP_PATH') ) {
            exit('APP_PATH not defined');
        }

        /** Read the configuration */
		if( ! file_exists(APP_PATH . '.env') ) {
			throw new \Exception("Configuration file not set. Contact support team.", 500);
		}

        ini_set("error_log", APP_PATH . 'storage/logs/errors/'.date('Y-m-d').'.log');
    }

    public function start()
    {
        $this->setup();
        (new IP())->isBlocked();

        $this->isMaintained();

        $config = new \Phalcon\Config\Adapter\Ini(APP_PATH . '.env');
		
		if( env('APP_ENV') == 'testing' ) {
			unset($config['APP_ENV']);
		}
		
		foreach($config as $key => $conf) {
			putenv($key.'='.$conf);
		}

        die(var_dump('starting app'));
    }
}