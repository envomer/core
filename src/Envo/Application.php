<?php

namespace Envo;

use Envo\API\Handler;
use Envo\Foundation\ExceptionHandler;
use Envo\Foundation\Loader;
use Envo\Foundation\Permission;
use Envo\Foundation\Config;
use Envo\Foundation\ApplicationTrait;
use Envo\Foundation\Router;

use Envo\Support\Str;
use Envo\Support\Translator;
use Phalcon\Cache\Backend\File;
use Phalcon\Cache\Frontend\Data as FrontendData;
use Phalcon\Db\Adapter\Pdo\Sqlite;
use Phalcon\Db\Profiler;
use Phalcon\Debug;
use Phalcon\DI;
use Phalcon\Di\FactoryDefault;
use Phalcon\Escaper;
use Phalcon\Events\Event;
use Phalcon\Events\Manager;
use Phalcon\Http\Response\Cookies;
use Phalcon\Http\Response;
use Phalcon\Http\Request;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Metadata\Files;
use Phalcon\Mvc\Url;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Model\Manager as ModelManager;
use Phalcon\Session\Adapter\Files as SessionAdapter;
use Phalcon\Events\Manager as EventManager;
use Phalcon\Mvc\View\Engine\Volt as VoltEngine;

use Envo\Support\IP;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Mvc\View\Engine\Php;

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
		require_once 'Helper.php';
		
		$this->setup();
		$this->setupEnv();
		$this->registerServices();
		$this->isInMaintenance();
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

		if(config('app.composer', true)) {
			require_once APP_PATH. DIRECTORY_SEPARATOR .'vendor'. DIRECTORY_SEPARATOR .'autoload.php';
		}
		
		if(env('APP_DEBUGBAR', false)) {
			$this->di->setShared('app', $this);
			
			/** @var Loader $loader */
			$loader = $this->di->get('autoloader');
			$loader->loadNamespace([
				'Snowair\Debugbar' => APP_PATH.'vendor/envome/debugbar/src/',
				'DebugBar' => APP_PATH.'vendor/maximebf/debugbar/src/Debugbar/',
				'Psr' => APP_PATH.'vendor/psr/log/Psr/',
				'Symfony\Component\VarDumper' => APP_PATH.'vendor/symfony/var-dumper/',
			]);
			
			(new \Snowair\Debugbar\ServiceProvider(APP_PATH . 'config/debugbar.php'))->start();
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
		//$di = new FactoryDefault();
		$debug = env('APP_ENV') === 'local' && env('APP_DEBUG');
		
		$this->registerNamespaces($di);
		
		$config = new Config();
		putenv('APP_VERSION=' . $config->get('app.version', '0.0.0'));

		/**
		 * Start the session the first time some component request the session service
		 */
		$di->setShared('session', function () use($config) {
			/** TODO: implement different session types [files, database, etc...found in config('session.driver')] **/
			session_save_path($config->get('session.files', APP_PATH.'storage/framework/sessions'));
			$session = new SessionAdapter(['uniqueId' => $config->get('session.prefix', 'envo')]);
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
		if($config->get('app.permissions.enabled')) {
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
		 * Translator
		 */
		$di->setShared('translator', Translator::class);
		
		/**
		 * Events manager
		 */
		$di->setShared('eventsManager', function() use($debug) {
			$eventManager = new Manager();
			if( ! $debug ) {
				$eventManager->attach('dispatch:beforeException', new ExceptionHandler);
			}

			return $eventManager;
		});

		/**
		 * Listen to dispatch
		 */
		$di->setShared('dispatcher', function() {
			$dispatcher = new Dispatcher();
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
		$di->setShared('router', function() use($di, $config, $debug) {
			$router = new Router(false);
			$router->apiPrefix = $config->get('app.api.prefix', 'api/v1');
			$api = $router->api(); // @TODO make the api better
			$router->setHandler($di->get('apiHandler'));
			require_once APP_PATH . 'app/routes.php';
			$router->mount($api);
			$router->extensions();

			$router->removeExtraSlashes(true);
			$router->setUriSource(Router::URI_SOURCE_SERVER_REQUEST_URI);

			return $router;
		});

		/**
		 * Register the VIEW component
		 */
		$di->setShared('view', function () {
			$view = new View();
			$view->setViewsDir(APP_PATH . 'app/Core/views/');
			$view->registerEngines(['.volt' => 'volt', '.php' => Php::class]);
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
			$metaData = new Files(array(
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

			$cache = new File($frontCache, array(
				'cacheDir' => APP_PATH . 'storage/framework/cache/'
			));

			return $cache;
		});
		
		/**
		 * Set URL component
		 */
		$url = new Url();
		$url->setBaseUri('/');
		$di->setShared('url', $url);
		
		if( $debug ) {
			$di->set('escaper', Escaper::class, true);
			$di->set('profiler', Profiler::class, true);
			
			//$debug = new Debug();
			//$debug->listen();
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
		ini_set('error_log', APP_PATH . 'storage/logs/errors/'.date('Y-m.W').'.log');
		
		define('ENVO_PATH', __DIR__ . '/../');
		
		if( ! defined('APP_PATH') ) {
			internal_exception('app.pathNotDefined', 500);
		}
		
		/**
		 * Read configuration file
		 */
		if(! file_exists(APP_PATH . '.env') ) {
			internal_exception('app.configurationFileNotFound', 500);
		}
	}
	
	/**
	 * Setup .env configuration
	 */
	public function setupEnv()
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
	 * @param \Phalcon\Di $di
	 *
	 * @return EventManager
	 */
	public function dbDebug($databaseName, $di)
	{
		// log the mysql queries if APP_DEBUG is set to true
		/** @var Profiler $profiler */
		$profiler = $di->get('profiler');
		$eventsManager = new EventManager();
		$eventsManager->collectResponses(true);
		
		// Listen all the database events
		$eventsManager->attach($databaseName, function(Event $event, $connection) use ($profiler) {
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
					$connection = new Sqlite($data);
				} else {
					$connection = new Mysql($data);
				}
				
				if( $debug ) {
					$connection->setEventsManager($self->dbDebug($key, $this));
				}
				
				return $connection;
			});
		}
		
		// @see https://docs.phalconphp.com/en/3.2/db-models#disabling-enabling-features
		Model::setup(
			[
				'disableAssignSetters' => true,
				'columnRenaming' => false,
				'exceptionOnFailedSave' => true
			]
		);
	}
	
	/**
	 * @param DI $di
	 */
	public function registerNamespaces(Di $di)
	{
		$loader = new \Phalcon\Loader();
		$namespaces = [];
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