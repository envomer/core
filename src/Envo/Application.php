<?php

namespace Envo;

use Envo\Foundation\Config;
use Envo\Foundation\Router;
use Phalcon\DI;
use Phalcon\Http\Response\Cookies;
use Phalcon\Mvc\View;
use Phalcon\Mvc\View\Engine\Php;
use Phalcon\Session\Adapter\Libmemcached;
use Phalcon\Session\Adapter\Redis;
use Phalcon\Session\Adapter\Stream;
use Phalcon\Session\Manager;
use Phalcon\Storage\AdapterFactory;
use Phalcon\Storage\SerializerFactory;

/**
 * Class Application
 * @package Envo
 */
class Application extends \Phalcon\Mvc\Application
{
    public const APP_ENV_TESTING = 'testing';
    public const APP_ENV_PRODUCTION = 'production';
    public const APP_ENV_STAGING = 'staging';
    public const APP_ENV_DEVELOPING = 'local';

	use ApplicationTrait;

	/**
	 * @var bool
	 */
	public $inMaintenance;

	/**
	 * @var bool
	 */
	public $initialized = false;

	/**
	 * @var bool
	 */
	//public $debug = false;

	/**
	 * Check if app is maintenance
	 *
	 * @return boolean
	 */
	public function isInMaintenance()
	{
		if ( $this->inMaintenance === null ) {
			$path = APP_PATH . 'storage/framework/down';
			$this->inMaintenance = file_exists($path) ? @file_get_contents($path) : false;

			if ( $this->inMaintenance ) {
				$this->inMaintenance = json_decode($this->inMaintenance);
			}
		}

		if ( $this->inMaintenance ) {
			$maintenance = $this->inMaintenance;
			$maintenance->retry = $maintenance->retry ?: 60;
			$maintenance->progress = abs(floor((($maintenance->time + $maintenance->retry) - time())/$maintenance->retry));
			$maintenance->progress = $maintenance->progress >= 98 ? 98 : $maintenance->progress;
			require ENVO_PATH . 'Envo/View/html/maintenance.php';
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
		if (!defined('APP_START')) {
			define('APP_START', microtime(true));
		}

		require_once 'Helper.php';

		$this->setup();
		$this->setupEnv();
		$this->registerServices();
		$this->isInMaintenance();

		$this->initialized = true;
	}

	/**
	 * @param bool $initialize
	 *
	 * @return self
	 * @throws \Exception
	 */
	public function start()
	{
		if (!$this->initialized) {
			$this->initialize();
		}

		if (defined('APP_CLI') && APP_CLI) {
			return $this;
		}

		//if (env('APP_DEBUGBAR', false)) {
			//$this->di->setShared('app', $this);
            //
			///** @var Loader $loader */
			//$loader = $this->di->get('autoloader');
			//$loader->loadNamespace([
			//	'Snowair\Debugbar' => APP_PATH.'vendor/envome/debugbar/src/',
			//	'DebugBar' => APP_PATH.'vendor/maximebf/debugbar/src/Debugbar/',
			//	'Psr' => APP_PATH.'vendor/psr/log/Psr/',
			//	'Symfony\Component\VarDumper' => APP_PATH.'vendor/symfony/var-dumper/',
			//]);
            //
			//(new \Snowair\Debugbar\ServiceProvider(APP_PATH . 'config/debugbar.php'))->start();
		//}

		try {
		    if (env( 'APP_ENV') === self::APP_ENV_TESTING){
		        return $this;
            }

			echo $this->handle($_SERVER["REQUEST_URI"])->getContent();
		} catch(\Exception $exception) {
			envo_exception_handler($exception);
		}
		
		return $this;
	}

	/**
	 * Register services
	 *
	 * @return DI
	 */
	public function registerServices()
	{
		$di = new DI();
		$this->debug = $debug = env('APP_DEBUG');

		$config = $this->initConfig($di);

		$this->sessionSetup($di, $config);

		$this->initCookies($di, $config);

		$this->registerBaseServices($config, $di);

		$this->initRouter($di, $config);
		$this->initView($di, $config);
		$this->initVolt($di, $config);
		$this->initDatabase($di, $config);

		$this->setDI($di);

		return $di;
	}

	/**
	 * @param DI     $di
	 * @param Config $config
	 */
	public function sessionSetup(Di $di, Config $config)
	{
		/**
		 * Start the session the first time some component request the session service
		 * TODO: export this logic into a Session class (Foundation/Session.php)
		 *
		 * TODO: implement different session types [files, database, etc...found in config('session.driver')]
		 */

		$di->setShared('session', function () use($config) {
			$driver = $config->get('session.driver', 'file');
            $serializerFactory = new SerializerFactory();
            $adapterFactory    = new AdapterFactory($serializerFactory);
            
            $session = new Manager();
            
            if ($driver === 'redis') {
                
                $adapter = new Redis($adapterFactory,[
					'prefix'     => $config->get('session.prefix', ''),
					'uniqueId'   => $config->get('database.uniqueId', ''),
					'lifetime'   => $config->get('session.lifetime', 120) * 60,
					'persistent' => $config->get('database.redis.default.persistent', false),
					'index'      => $config->get('database.redis.default.database', 0),
					'auth'       => $config->get('database.redis.default.auth', ''),
					'port'       => $config->get('database.redis.default.port', 6379),
					'host'       => $config->get('database.redis.default.host', '127.0.0.1'),
				]);
    
			} else if ($driver === 'memcache') {
				$adapter = new Libmemcached($adapterFactory,[
					'uniqueId'   => $config->get('database.uniqueId', ''),
					'host'       => $config->get('database.host', '127.0.0.1'),
					'port'       => $config->get('database.port', 11211),
					'persistent' => $config->get('database.persistent', true),
					'lifetime'   => $config->get('session.lifetime', 3600),
					'prefix'     => $config->get('session.prefix', ''),
				]);
			} else {

				session_save_path($config->get('session.files', APP_PATH.'storage/framework/sessions'));
				$adapter = new Stream([
					'uniqueId' => $config->get('session.prefix', '')
				]);
			}

            $session->setAdapter($adapter);
			if ($session->status() !== Manager::SESSION_ACTIVE) {
                $session->start();
            }

			return $session;
		});
	}

	/**
	 * @param DI $di
	 * @param Config $config
	 */
	private function initRouter(DI $di, Config $config): void
	{
		/**
		 * Register the router
		 */
		$di->setShared('router', function () use ($di, $config) {
			$router = new Router(false);
			$router->removeExtraSlashes(true);
			//$router->setUriSource(Router::URI_SOURCE_SERVER_REQUEST_URI);

            $routesPath = APP_PATH . 'bootstrap/cache/routes.php';
			if ( file_exists($routesPath) ) {
				$routes = require $routesPath;
				$router->import($routes);

				if ( isset($routes['apis']) && $routes['apis'] ) {
					$di->get('apiHandler')->setApis($routes['apis']);
				}

				return $router;
			}

			$appConfig = $config->get('app.api', []);

			if ( !isset($appConfig['enabled']) || $appConfig['enabled'] ) {
				$router->apiPrefix = $appConfig['prefix'] ?? $router->apiPrefix;
				$api = $router->api();
				$router->setHandler($di->get('apiHandler'));
			}

			require_once APP_PATH . 'app/routes.php';

			if ( isset($api) ) {
				$router->mount($api);
			}

			$router->extensions();

			return $router;
		});
	}

	/**
	 * @param DI $di
	 * @param Config $config
	 */
	private function initView(DI $di, Config $config): void
	{
		/**
		 * Register the VIEW component
		 */
		$di->setShared('view', function () use ($config) {
			$view = new View();

			$view->setViewsDir($config->get('view.defaultDirectory', [
				APP_PATH . 'app/Core/views/',
				APP_PATH . 'app/Core/Template/'
			]));

			$engines = ['.php' => Php::class];
			if ( $config->get('view.volt.enabled', false) ) {
				$engines['.volt'] = 'volt';

			}
			$view->registerEngines($engines);

			return $view;
		});
	}

	/**
	 * @param Config $config
	 * @param DI $di
	 */
	private function initVolt(DI $di, Config $config): void
	{
		$voltConfig = $config->get('view.volt');

		if ( !isset($voltConfig['enabled']) || ! $voltConfig['enabled'] ) {
			return;
		}

		$di->setShared('volt', function ($view, $di) use ($config, $voltConfig) {
			$volt = new \Phalcon\Mvc\View\Engine\Volt($view, $di);

			$volt->setOptions([
				'compiledPath' => $voltConfig['compiledPath'] ?? '',
				'stat' => $voltConfig['stat'] ?? true,
				'prefix' => $voltConfig['prefix'] ?? '',
				'compiledSeparator' => $voltConfig['compiledSeparator'] ?? '%%',
				'compiledExtension' => $voltConfig['compiledExtension'] ?? '.php',
				'compileAlways' => $voltConfig['compileAlways'] ?? false,
				'autoescape' => $voltConfig['autoescape'] ?? false,
			]);

			// TODO: we need to move the volt functions. causes trouble with config:cache command (closures)!!
			$compiler = $volt->getCompiler();
			if ( isset($voltConfig['functions']) && is_array($voltConfig['functions']) ) {
				foreach ($voltConfig['functions'] as $functionName => $function) {
					$compiler->addFunction($functionName, $function);
				}
			}

			return $volt;
		});
	}

	/**
	 * @param DI $di
	 * @param Config $config
	 */
	private function initCookies(DI $di, Config $config): void
	{
		/**
		 * Enable cookies
		 */
		$di->setShared('cookies', function () use ($config) {
			$cookies = new Cookies();
			$settings = $config->get('app.cookies', []);
			$cookies->useEncryption($settings['encryption'] ?? false);
			$cookies->setSignKey($settings['sign_key'] ?? null);

			return $cookies;
		});
	}
}
