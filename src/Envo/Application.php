<?php

namespace Envo;

use Envo\Foundation\ExceptionHandler;
use Envo\Foundation\ApplicationTrait;
use Envo\Support\Str;

use Phalcon\Cache\Frontend\Data as FrontendData;
use Phalcon\Db\Adapter\Pdo\Mysql as Database;
use Phalcon\DI\FactoryDefault;
use Phalcon\DI;
use Phalcon\Http\Response\Cookies;
use Phalcon\Http\Response;
use Phalcon\Http\Request;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Model\Manager;
use Phalcon\Session\Adapter\Files as SessionAdapter;

class Application extends \Phalcon\Mvc\Application
{
	use ApplicationTrait;

	public $inMaintenance = null;

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
			$maintenance->progress = abs(floor(((($maintenance->time + $maintenance->retry) - time())/$maintenance->retry)));
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
	 */
	public function start()
	{
		error_reporting(-1);
		set_exception_handler('envo_exception_handler');
		set_error_handler('envo_error_handler');

		$this->setup();
		$this->setupConfig();
		$this->isInMaintenance();
		$this->registerServices();

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
		$instance = $this;
		$debug = env('APP_ENV') === 'local' && env('APP_DEBUG', false);

		/**
		 * Start the session the first time some component request the session service
		 */
		$di->setShared('session', function () {
			session_save_path(APP_PATH.'storage/framework/sessions');
			$session = new SessionAdapter(array('uniqueId' => 'envo-session'));
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
		 * Set request
		 */
		$di->setShared('request', Request::class);

		/**
		 * Set response
		 */
		$di->setShared('response', Response::class);

		/**
		 * Set models manager
		 */
		$di->setShared('modelsManager', Manager::class);

		/**
		 * Custom authentication component
		 */
		$di->setShared('auth', Auth::class);

		$di->set('eventsManager', function() use($debug) {
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

		$di->setShared('apiHandler', function() {
			return new \Envo\API\Handler();
		});

		/**
		 * Register the router
		 */
		$di->setShared('router', function() use($di) {
			$router = new \Envo\Foundation\Router(false);
			$api = $router->api();
			$router->setHandler($di->get('apiHandler'));
			require_once APP_PATH . 'app/routes.php';
			$router->mount($api);

			$router->removeExtraSlashes(true);
			$router->setUriSource(\Phalcon\Mvc\Router::URI_SOURCE_SERVER_REQUEST_URI);

			return $router;
		});

		/**
		 * Register the VIEW component
		 */
		$di->setShared('view', function () {
			$view = new View();
			$view->setViewsDir(APP_PATH . 'app/Core/View/');
			// $view->registerEngines(array('php' => "Phalcon\Mvc\View\Engine\Php"));
			return $view;
		});

		/**
		 * Set the database configuration
		 */
		$di->setShared('db', function () use($debug, $instance) {
			$databaseConfig = require(APP_PATH . 'config/database.php');

			
			if( $databaseConfig['default'] === 'sqlite' ) {
				$connection = new \Phalcon\Db\Adapter\Pdo\Sqlite($databaseConfig['connections'][$databaseConfig['default']]);
			} else {
				$connection = new Database($databaseConfig['connections'][$databaseConfig['default']]);
			}

			if( $debug ) {
				$connection->setEventsManager($instance->dbDebug($this));
			}

			return $connection;
		});

		/**
		* If the configuration specify the use of metadata adapter use it or use memory otherwise
		*/
		$di->set('modelsMetadata', function () {
			$metaData = new \Phalcon\Mvc\Model\Metadata\Files(array(
				'metaDataDir' => APP_PATH . 'storage/framework/cache/'
			));

			return $metaData;
		});

		/**
		 * Set the models cache service
		 */
		$di->set('modelsCache', function () {
			// Cache data for one day by default
			$frontCache = new FrontendData(["lifetime" => 86400]);

			$cache = new Phalcon\Cache\Backend\File($frontCache, array(
				"cacheDir" => APP_PATH . 'storage/framework/cache/'
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

	public function dbDebug($di)
	{
		// log the mysql queries if APP_DEBUG is set to true
		// $logger = new \Phalcon\Logger\Adapter\File( APP_PATH . 'storage/logs/db-'.date('Y-m-d').'.log');
		$profiler = $di->getProfiler();
	  	$eventsManager = new \Phalcon\Events\Manager();
		// Listen all the database events
		$eventsManager->attach('db', function($event, $connection) use ($profiler) {
			if ($event->getType() == 'beforeQuery') {
				// $traces = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
				$profiler->startProfile($connection->getSQLStatement());
				if(isset($_GET['cc2'])) {
					$ignoreClasses = ['Phalcon\\', 'Application'];
					$path = '';
					foreach(debug_backtrace() as $trace) {
						if( isset($trace['class']) && Str::strposa($trace['class'], $ignoreClasses) ) continue;
						$path .= (isset($trace['class']) ? $trace['class'] : '') . '::' .$trace['function'].';';
					}
					var_dump($connection->getSQLStatement(), $connection->getSQLVariables());
					echo "Execution Time: {$profiler->getTotalElapsedSeconds()}. <br> PATH: {$path}\n\r";
					echo '<br><br>-----------------------------------------------------------------------<br><br>';
				} else {
					// $logger->log($connection->getSQLStatement() . " [Execution Time: {$profiler->getTotalElapsedSeconds()}. PATH: {$path}]\n\r", Phalcon\Logger::DEBUG);
				}
			}
			if ($event->getType() == 'afterQuery') {
				$profiler->stopProfile();
			}
		});

		return $eventsManager;
	}
}