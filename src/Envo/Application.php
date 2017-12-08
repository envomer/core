<?php

namespace Envo;

use Envo\API\Handler;
use Envo\Foundation\ExceptionHandler;
use Envo\Foundation\Permission;
use Envo\Foundation\Config;
use Envo\Foundation\ApplicationTrait;
use Envo\Foundation\Router;

use Phalcon\Cache\Frontend\Data as FrontendData;
use Phalcon\DI;
use Phalcon\Http\Response\Cookies;
use Phalcon\Http\Response;
use Phalcon\Http\Request;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Model\Manager as ModelManager;
use Phalcon\Session\Adapter\Files as SessionAdapter;
use Phalcon\Events\Manager as EventManager;
use Phalcon\Mvc\View\Engine\Volt as VoltEngine;

use Envo\Support\IP;
use Phalcon\Db\Adapter\Pdo\Mysql;

/**
 * Class Application
 * @package Envo
 */
class Application extends \Phalcon\Mvc\Application
{
	//use ApplicationTrait;

	public $inMaintenance;

	/**
	 * Check if app is maintenance
	 *
	 * @return boolean
	 */
	public function isInMaintenance()
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
			$maintenance->progress = abs(floor((($maintenance->time + $maintenance->retry) - time())/$maintenance->retry));
			$maintenance->progress = $maintenance->progress >= 98 ? 98 : $maintenance->progress;
			require ENVO_PATH . 'View/html/maintenance.php';
			die;
		}

		return false;
	}
	
	/**
	 * Start app
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function initialize()
	{
		define('APP_START', microtime(true));
		require 'Helper.php';
		
		$this->setup();
		$this->setupConfig();
		$di = $this->registerServices();
		$this->isInMaintenance();
		
		if(env('APP_DEBUGBAR', false)) {
			$di->setShared('app', $this);
			(new \Snowair\Debugbar\ServiceProvider(APP_PATH . 'config/debugbar.php'))->start();
		}
	}
	
	/**
	 * @param bool $initialize
	 *
	 * @throws \Exception
	 */
	public function start($initialize = true)
	{
		if($initialize) {
			$this->initialize();
		}
		
		echo $this->handle()->getContent();
	}

	/**
	 * Register services
	 *
	 * @return DI
	 */
	public function registerServices()
	{
		$di = new DI();
		$debug = env('APP_ENV') === 'local' && env('APP_DEBUG');
		
		$this->registerNamespaces($di);

		/**
		 * Start the session the first time some component request the session service
		 */
		$di->setShared('session', function () {
			session_save_path(APP_PATH.'storage/framework/sessions');
			$session = new SessionAdapter(['uniqueId' => 'envo-session']);
			$session->start();
			return $session;
		});

		/**
		 * Enable cookies
		 */
		$di->setShared('cookies', function () {
			$cookies = new Cookies();
			$cookies->useEncryption(false);
			return $cookies;
		});

		/**
		 * Set config
		 */
		$config = new Config();
		$di->setShared('config', $config);

		/**
		 * Set request
		 */
		$di->setShared('request', Request::class);

		/**
		 * Set response
		 */
		$di->setShared('response', Response::class);
		
		/**
		 * Set permission
		 */
		if(config('app.permissions.enabled')) {
			$di->setShared('permission', Permission::class);
		}

		/**
		 * Set models manager
		 */
		$di->setShared('modelsManager', ModelManager::class);

		/**
		 * Custom authentication component
		 */
		$di->setShared('auth', Auth::class);
		
		/**
		 * Events manager
		 */
		$di->setShared('eventsManager', function() use($debug) {
			$eventManager = new \Phalcon\Events\Manager();
			if( ! $debug ) {
				$eventManager->attach('dispatch:beforeException', new ExceptionHandler);
			}

			return $eventManager;
		});

		/**
		 * Listen to dispatch
		 */
		$di->setShared('dispatcher', function() {
			$dispatcher = new \Phalcon\Mvc\Dispatcher();
			$dispatcher->setEventsManager($this->get('eventsManager'));
			$dispatcher->setDefaultNamespace("Core\Controller\\");
			return $dispatcher;
		});
		
		/**
		 * Initialize API handler
		 */
		$di->setShared('apiHandler', Handler::class);

		/**
		 * Register the router
		 */
		$di->setShared('router', function() use($di, $debug) {
			$router = new Router(false);
			$api = $router->api();
			$router->setHandler($di->get('apiHandler'));
			require_once APP_PATH . 'app/routes.php';
			$router->mount($api);

			$router->removeExtraSlashes(true);
			$router->setUriSource(Router::URI_SOURCE_SERVER_REQUEST_URI);

			return $router;
		});

		/**
		 * Register the VIEW component
		 */
		//$di->setShared('view', function () {
		//	$view = new View();
		//	$view->setViewsDir(APP_PATH . 'app/Core/View/');
		//	// $view->registerEngines(array('php' => "Phalcon\Mvc\View\Engine\Php"));
		//	return $view;
		//});
		
		$di->setShared('view', function () {
			$view = new View();
			$view->setViewsDir(APP_PATH . 'app/Core/views/');
			$view->registerEngines(array('.volt' => 'volt', 'php' => "Phalcon\Mvc\View\Engine\Php"));
			return $view;
		});

		/**
		 * Set the database configuration
		 */
		$this->registerDatabases($di, $debug);

		/**
		* If the configuration specify the use of metadata adapter use it or use memory otherwise
		*/
		$di->setShared('modelsMetadata', function () {
			$metaData = new \Phalcon\Mvc\Model\Metadata\Files(array(
				'metaDataDir' => APP_PATH . 'storage/framework/cache/'
			));

			return $metaData;
		});

		/**
		 * Set the models cache service
		 */
		$di->setShared('modelsCache', function () {
			// Cache data for one day by default
			$frontCache = new FrontendData(['lifetime' => 86400]);

			$cache = new \Phalcon\Cache\Backend\File($frontCache, array(
				'cacheDir' => APP_PATH . 'storage/framework/cache/'
			));

			return $cache;
		});

		if( $debug ) {
			$di->set('url', \Phalcon\Mvc\Url::class);
			$di->set('escaper', \Phalcon\Escaper::class);
			$di->set('profiler', \Phalcon\Db\Profiler::class, true);
			
			$debug = new \Phalcon\Debug();
			$debug->listen();
		}

		$this->setDI($di);

		return $di;
	}
	
	/**
	 * Define error logging and check if .env file exists
	 *
	 * @throws \Exception
	 */
	public function setup()
	{
		error_reporting(-1);
		set_exception_handler('envo_exception_handler');
		set_error_handler('envo_error_handler');
		
		//define('APP_START', microtime(true));
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
		//(new IP())->isBlocked();
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
	 * @return EventManager
	 */
	public function dbDebug($databaseName, $di)
	{
		// log the mysql queries if APP_DEBUG is set to true
		// $logger = new \Phalcon\Logger\Adapter\File( APP_PATH . 'storage/logs/db-'.date('Y-m-d').'.log');
		$profiler = $di->getProfiler();
		$eventsManager = new EventManager();
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
					$connection = new Mysql($data);
				}
				
				if( $debug ) {
					$connection->setEventsManager($self->dbDebug($key, $this));
				}
				
				return $connection;
			});
		}
	}
	
	/**
	 * @param DI $di
	 */
	public function registerNamespaces(Di $di)
	{
		$loader = new \Phalcon\Loader();
		//$loader->registerDirs([ APP_PATH . 'library', APP_PATH . 'services']);
		$namespaces = [];
		//$namespaces['Phalcon\Http'] = APP_PATH . 'vendor/phalcon/incubator/Library/Phalcon/Http';
		$namespaces['Envo'] = [
			ENVO_PATH . 'Envo',
			ENVO_PATH . 'Envo/Abstract'
		];
		
		$loader->registerNamespaces($namespaces);
		$loader->register();
		
		$autoloader = new \Envo\Foundation\Loader($loader);
		$di->setShared('autoloader', $autoloader);
	}
}