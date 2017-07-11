<?php

namespace Envo;

use Envo\Foundation\IP;
use Envo\Foundation\ApplicationTrait;

use Phalcon\Cache\Frontend\Data as FrontendData;
use Phalcon\Db\Adapter\Pdo\Mysql as Database;
use Phalcon\DI\FactoryDefault;
use Phalcon\Http\Response\Cookies;
use Phalcon\Mvc\View;
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
        (new IP())->isBlocked();

        $this->isMaintained();
		$this->setupConfig();
		$this->registerServices();

        echo $this->handle()->getContent();
    }

	public function registerServices()
	{
		$di = new FactoryDefault();

		/**
		 * Start the session the first time some component request the session service
		 */
		$di->set('session', function () {
			session_save_path(APP_PATH.'storage/framework/sessions');
			$session = new SessionAdapter(array('uniqueId' => 'envo-session'));
			$session->start();
			return $session;
		});

		/**
		 * Enable cookies
		 */
		$di->set('cookies', function () {
			$cookies = new Cookies();
			$cookies->useEncryption(false);
			return $cookies;
		});

		/**
		 * Custom authentication component
		 */
		$di->set('auth', function () {
			return Auth::getInstance();
		});

		/**
		 * Listen to dispatch
		 */
		$di->setShared('dispatcher', function() {
			$eventManager = new \Phalcon\Events\Manager();
			// $eventManager->attach('dispatch:beforeException', new NotFound);

			$dispatcher = new \Phalcon\Mvc\Dispatcher();
			$dispatcher->setEventsManager($eventManager);
			$dispatcher->setDefaultNamespace("App\Core\Controller\\");
			return $dispatcher;
		});

		/**
		 * Register the router
		 */
		$di->set('router', function() {
			$router = new \Envo\Foundation\Router(false);
			require APP_PATH . 'app/routes.php';
			$router->api();

			return $router;
		});

		/**
		 * Register the VIEW component
		 */
		$di->set('view', function () {
			$view = new View();
			$view->setViewsDir(APP_PATH . 'app/Core/views/');
			$view->registerEngines(array('php' => "Phalcon\Mvc\View\Engine\Php"));
			return $view;
		});

		/**
		 * Set the database configuration
		 */
		$di->set('db', function () {
			$databaseConfig = require(APP_PATH . 'config/database.php');
			$connection = new Database($databaseConfig['connections'][$databaseConfig['default']]);
			return $connection;
		});

		$this->setDI($di);
	}
}