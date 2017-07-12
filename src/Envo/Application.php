<?php

namespace Envo;

use Envo\Foundation\IP;
use Envo\Foundation\ExceptionHandler;
use Envo\Foundation\ApplicationTrait;

use Phalcon\Cache\Frontend\Data as FrontendData;
use Phalcon\Db\Adapter\Pdo\Mysql as Database;
use Phalcon\DI\FactoryDefault;
use Phalcon\DI;
use Phalcon\Http\Response\Cookies;
use Phalcon\Http\Request;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Model\Manager;
use Phalcon\Session\Adapter\Files as SessionAdapter;

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
		$this->setupConfig();
		
		$this->isMaintained();

		$this->registerServices();

		echo $this->handle()->getContent();
	}

	public function registerServices()
	{
		$di = new DI();
		// $di = new FactoryDefault();
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

		$di->setShared('request', Request::class);
		$di->setShared('modelsManager', Manager::class);

		/**
		 * Custom authentication component
		 */
		$di->setShared('auth', function () {
			return Auth::getInstance();
		});

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
			$dispatcher->setDefaultNamespace("App\Core\Controller\\");
			return $dispatcher;
		});

		/**
		 * Register the router
		 */
		$di->setShared('router', function() {
			$router = new \Envo\Foundation\Router(false);
			require_once APP_PATH . 'app/routes.php';
			$router->api();

			return $router;
		});

		/**
		 * Register the VIEW component
		 */
		$di->setShared('view', function () {
			$view = new View();
			$view->setViewsDir(APP_PATH . 'app/Core/views/');
			$view->registerEngines(array('php' => "Phalcon\Mvc\View\Engine\Php"));
			return $view;
		});

		/**
		 * Set the database configuration
		 */
		$di->setShared('db', function () {
			$databaseConfig = require(APP_PATH . 'config/database.php');
			if( $databaseConfig['default'] === 'sqlite' ) {
				$connection = new \Phalcon\Db\Adapter\Pdo\Sqlite($databaseConfig['connections'][$databaseConfig['default']]);
			} else {
				$connection = new Database($databaseConfig['connections'][$databaseConfig['default']]);
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
			
			$debug = new \Phalcon\Debug();
			$debug->listen();
		}

		$this->setDI($di);
	}
}