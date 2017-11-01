<?php

namespace Envo\Foundation;

use Envo\Support\IP;
use Phalcon\Di;
use Phalcon\Db\Adapter\Pdo\Mysql as Database;
use Phalcon\Events\Manager;

/**
 * Trait ApplicationTrait
 *
 * @package Envo\Foundation
 */
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
	
	/**
	 * Debug database
	 *
	 * @param $databaseName
	 * @param $di
	 *
	 * @return Manager
	 */
	public function dbDebug($databaseName, $di)
	{
		// log the mysql queries if APP_DEBUG is set to true
		// $logger = new \Phalcon\Logger\Adapter\File( APP_PATH . 'storage/logs/db-'.date('Y-m-d').'.log');
		$profiler = $di->getProfiler();
		$eventsManager = new Manager();
		// Listen all the database events
		$eventsManager->attach($databaseName, function($event, $connection) use ($profiler) {
			if ($event->getType() === 'beforeQuery') {
				// $traces = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
				$profiler->startProfile($connection->getSQLStatement());
				if(isset($_GET['cc2'])) {
					$ignoreClasses = ['Phalcon\\', 'Application'];
					$path = '';
					foreach(debug_backtrace() as $trace) {
						if( isset($trace['class']) && Str::strposa($trace['class'], $ignoreClasses) ){
							continue;
						}
						$path .= (isset($trace['class']) ? $trace['class'] : '') . '::' .$trace['function'].';';
					}
					var_dump($connection->getSQLStatement(), $connection->getSQLVariables());
					echo "Execution Time: {$profiler->getTotalElapsedSeconds()}. <br> PATH: {$path}\n\r";
					echo '<br><br>-----------------------------------------------------------------------<br><br>';
				}
			}
			if ($event->getType() === 'afterQuery') {
				$profiler->stopProfile();
			}
		});
		
		return $eventsManager;
	}
	
	/**
	 * Register database connections
	 *
	 * @param DI $di
	 * @param bool $debug
	 */
	public function registerDatabases(DI $di, $debug = false)
	{
		$databaseConfig = config('database');
		$connections = ['db' => $databaseConfig['default']];
		if(isset($databaseConfig['use'])) {
			/** @var array $databaseConfig */
			foreach ($databaseConfig['use'] as $item){
				$connections[$item] = $item;
			}
		}
		
		$self = $this;
		foreach ($connections as $key => $connectionName){
			$di->setShared($key, function () use($debug, $databaseConfig, $key, $connectionName, $self) {
				$data = $databaseConfig['connections'][$connectionName];
				
				if( $data['driver'] === 'sqlite' ) {
					$connection = new \Phalcon\Db\Adapter\Pdo\Sqlite($data);
				} else {
					$connection = new Database($data);
				}
				
				if( $debug ) {
					$connection->setEventsManager($self->dbDebug($key, $this));
				}
				
				return $connection;
			});
		}
	}
}