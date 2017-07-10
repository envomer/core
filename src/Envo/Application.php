<?php

namespace Envo;

use Envo\Foundation\IP;
use Envo\Foundation\ApplicationTrait;

class Application extends \Phalcon\Mvc\Application
{
	use ApplicationTrait;

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

    public function start()
    {
        $this->setup();
        (new IP())->isBlocked();

        $this->isMaintained();
		$this->setupConfig();
		require_once ('helpers.php');

        die(var_dump('starting app'));
    }
}