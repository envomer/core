<?php

namespace Envo;

use Envo\API\Handler;
use Envo\Foundation\Cache;
use Envo\Foundation\Config;
use Envo\Foundation\ExceptionHandler;
use Envo\Foundation\Loader;
use Envo\Foundation\Permission;
use Envo\Foundation\Router;
use Envo\Support\Str;
use Envo\Support\Translator;

use Phalcon\Cache\Backend\File;
use Phalcon\Cache\Frontend\Data as FrontendData;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Db\Adapter\Pdo\Sqlite;
use Phalcon\Db\Profiler;
use Phalcon\DI;
use Phalcon\Escaper;
use Phalcon\Events\Event;
use Phalcon\Events\Manager;
use Phalcon\Events\Manager as EventManager;
use Phalcon\Http\Request;
use Phalcon\Http\Response;
use Phalcon\Http\Response\Cookies;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Manager as ModelManager;
use Phalcon\Mvc\Model\Metadata\Files;
use Phalcon\Mvc\Router\Route;
use Phalcon\Mvc\Url;
use Phalcon\Mvc\View;
use Phalcon\Mvc\View\Engine\Php;

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
        if ($this->inMaintenance === null) {
            $path = APP_PATH . 'storage/framework/down';
            $this->inMaintenance = file_exists($path) ? @file_get_contents($path) : false;

            if ($this->inMaintenance) {
                $this->inMaintenance = json_decode($this->inMaintenance);
            }
        }

        if ($this->inMaintenance) {
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
     * @return bool
     * @throws \Exception
     */
    public function start()
    {
        if (!$this->initialized) {
            $this->initialize();
        }

        if (defined('APP_CLI') && APP_CLI) {
            return true;
        }

        //if (env('APP_DEBUGBAR', false)) {
            //$this->di->setShared('app', $this);
            //
            ///** @var Loader $loader */
            //$loader = $this->di->get('autoloader');
            //$loader->loadNamespace([
            //  'Snowair\Debugbar' => APP_PATH.'vendor/envome/debugbar/src/',
            //  'DebugBar' => APP_PATH.'vendor/maximebf/debugbar/src/Debugbar/',
            //  'Psr' => APP_PATH.'vendor/psr/log/Psr/',
            //  'Symfony\Component\VarDumper' => APP_PATH.'vendor/symfony/var-dumper/',
            //]);
            //
            //(new \Snowair\Debugbar\ServiceProvider(APP_PATH . 'config/debugbar.php'))->start();
        //}

        try {
            if (env('APP_ENV') === self::APP_ENV_TESTING) {
                return $this;
            }

            echo $this->handle()->getContent();
        } catch (\Exception $exception) {
            envo_exception_handler($exception);
        }
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
     * Define error logging and check if .env file exists
     *
     * @throws \Exception
     */
    //public function setup()
    //{
    //  error_reporting(-1);
    //  set_exception_handler('envo_exception_handler');
    //  set_error_handler('envo_error_handler');
    //
    //  ini_set('error_log', APP_PATH . 'storage/framework/logs/errors/'.date('Y-m.W').'.log');
    //
    //  if (!defined('ENVO_PATH')) {
    //      define('ENVO_PATH', __DIR__ . '/../');
    //  }
    //
    //  if ( ! defined('APP_PATH') ) {
    //      throw new \Exception('app.appPathNotDefined', 500);
    //  }
    //
    //  /**
    //   * Read configuration file
    //   */
    //  if (! file_exists(APP_PATH . '.env') ) {
    //      throw new \Exception('app.envConfigurationFileNotFound', 500);
    //  }
    //
    //  require_once APP_PATH. DIRECTORY_SEPARATOR .'vendor'. DIRECTORY_SEPARATOR .'autoload.php';
    //}

    /**
     * Setup .env configuration
     */
    //public function setupEnv()
    //{
    //  $config = parse_ini_file(APP_PATH . '.env');
    //
    //  if ( getenv('APP_ENV') === 'testing' ) {
    //      unset($config['APP_ENV']);
    //  }
    //
    //  foreach($config as $key => $conf) {
    //      if ( is_array($conf) ) {
    //          continue;
    //      }
    //      putenv($key.'='.$conf);
    //  }
    //}

    ///**
    // * Debug database
    // *
    // * @param $databaseName
    // * @param \Phalcon\Di $di
    // *
    // * @return EventManager
    // */
    //public function dbDebug($databaseName, $di)
    //{
    //  if (!$this->debug) {
    //      return;
    //  }
    //
    //  // log the mysql queries if APP_DEBUG is set to true
    //  /** @var Profiler $profiler */
    //  $profiler = $di->get('profiler');
    //  $eventsManager = new EventManager();
    //  $eventsManager->collectResponses(true);
    //
    //  // Listen all the database events
    //  //$requestDebug = isset($_GET['cc2']); // add this to config or so...
    //  $logger = new \Phalcon\Logger\Adapter\File( APP_PATH . 'storage/framework/logs/db/db-'.date('Y-m-d').'.log');
    //  $eventsManager->attach($databaseName, function(Event $event, $connection) use ($profiler, $logger) {
    //      if ($event->getType() === 'beforeQuery') {
    //          // $traces = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
    //          $profiler->startProfile($connection->getSQLStatement());
    //          $item = $profiler->getLastProfile();
    //          $item->setSqlVariables($connection->getSqlVariables() ?: []);
    //          $item->setSqlBindTypes($connection->getSqlBindTypes() ?: []);
    //
    //          //if ($requestDebug) {
    //              $ignoreClasses = ['Phalcon\\', 'Application'];
    //              $path = '';
    //              foreach(debug_backtrace() as $trace) {
    //                  if ( isset($trace['class']) && Str::strposa($trace['class'], $ignoreClasses) ){
    //                      continue;
    //                  }
    //                  $path .= (isset($trace['class']) ? $trace['class'] : '') . '::' .$trace['function'].';';
    //              }
    //              //var_dump($connection->getSQLStatement(), $connection->getSQLVariables());
    //              //echo "Execution Time: {$profiler->getTotalElapsedSeconds()}. <br> PATH: {$path}\n\r";
    //              //echo '<br><br>-----------------------------------------------------------------------<br><br>';
    //              $logger->log($connection->getSQLStatement() . " [Execution Time: {$profiler->getTotalElapsedSeconds()}. PATH: {$path}]\n\r", \Phalcon\Logger::DEBUG);
    //          //}
    //      }
    //      if ($event->getType() === 'afterQuery') {
    //          $profiler->stopProfile();
    //      }
    //  });
    //
    //  return $eventsManager;
    //}

    ///**
    // * Register database connections
    // *
    // * @param DI $di
    // * @param bool $debug
    // */
    //public function registerDatabases(DI $di, $logging = false)
    //{
    //  $databaseConfig = config('database');
    //  $connections = ['db' => $databaseConfig['default']];
    //  if (isset($databaseConfig['use'])) {
    //      /** @var array $databaseConfig */
    //      foreach ($databaseConfig['use'] as $item){
    //          $connections[$item] = $item;
    //      }
    //  }
    //
    //  $self = $this;
    //  foreach ($connections as $key => $connectionName){
    //      $di->setShared($key, function () use($logging, $databaseConfig, $key, $connectionName, $self) {
    //          $data = $databaseConfig['connections'][$connectionName];
    //
    //          if ( $data['driver'] === 'sqlite' ) {
    //              $connection = new Sqlite($data);
    //          } else {
    //              $connection = new Mysql($data);
    //          }
    //
    //          if ( $logging && $self->debug ) {
    //              $connection->setEventsManager($self->dbDebug($key, $this));
    //          }
    //
    //          return $connection;
    //      });
    //  }
    //
    //  // @see https://docs.phalconphp.com/en/3.2/db-models#disabling-enabling-features
    //  $dbConfig = config('database');
    //
    //  Model::setup([
    //      'astCache'              => $dbConfig['astCache'] ?? null,
    //      'cacheLevel'            => $dbConfig['cacheLevel'] ?? 3,
    //      'castOnHydrate'         => $dbConfig['castOnHydrate'] ?? false,
    //      'columnRenaming'        => $dbConfig['columnRenaming'] ?? true,
    //      'disableAssignSetters'  => $dbConfig['disableAssignSetters'] ?? false,
    //      'enableImplicitJoins'   => $dbConfig['enableImplicitJoins'] ?? true,
    //      'enableLiterals'        => $dbConfig['enableLiterals'] ?? true,
    //      'escapeIdentifiers'     => $dbConfig['escapeIdentifiers'] ?? true,
    //      'events'                => $dbConfig['events'] ?? true,
    //      'exceptionOnFailedSave' => $dbConfig['exceptionOnFailedSave'] ?? true,
    //      'forceCasting'          => $dbConfig['forceCasting'] ?? false,
    //      'ignoreUnknownColumns'  => $dbConfig['ignoreUnknownColumns'] ?? false,
    //      'lateStateBinding'      => $dbConfig['lateStateBinding'] ?? false,
    //      'notNullValidations'    => $dbConfig['notNullValidations'] ?? true,
    //      'parserCache'           => $dbConfig['parserCache'] ?? null,
    //      'phqlLiterals'          => $dbConfig['phqlLiterals'] ?? true,
    //      'uniqueCacheId'         => $dbConfig['uniqueCacheId'] ?? 3,
    //      'updateSnapshotOnSave'  => $dbConfig['updateSnapshotOnSave'] ?? true,
    //      'virtualForeignKeys'    => $dbConfig['virtualForeignKeys'] ?? true,
    //  ]);
    //}

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

        $di->setShared('session', function () use ($config) {
            $driver = $config->get('session.driver', 'file');

            if ($driver === 'redis') {
                $session = new \Phalcon\Session\Adapter\Redis([
                    'prefix'     => $config->get('session.prefix', ''),
                    'uniqueId'   => $config->get('database.uniqueId', ''),
                    'lifetime'   => $config->get('session.lifetime', 120) * 60,
                    'persistent' => $config->get('database.redis.default.persistent', false),
                    'index'      => $config->get('database.redis.default.database', 0),
                    'auth'       => $config->get('database.redis.default.auth', ''),
                    'port'       => $config->get('database.redis.default.port', 6379),
                    'host'       => $config->get('database.redis.default.host', '127.0.0.1'),
                ]);
            } elseif ($driver === 'memcache') {
                $session = new \Phalcon\Session\Adapter\Memcache([
                    'uniqueId'   => $config->get('database.uniqueId', ''),
                    'host'       => $config->get('database.host', '127.0.0.1'),
                    'port'       => $config->get('database.port', 11211),
                    'persistent' => $config->get('database.persistent', true),
                    'lifetime'   => $config->get('session.lifetime', 3600),
                    'prefix'     => $config->get('session.prefix', ''),
                ]);
            } else {
                session_save_path($config->get('session.files', APP_PATH.'storage/framework/sessions'));
                $session = new \Phalcon\Session\Adapter\Files([
                    'uniqueId' => $config->get('session.prefix', '')
                ]);
            }

            if (!$session->isStarted()) {
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
            $router->setUriSource(Router::URI_SOURCE_SERVER_REQUEST_URI);

            if (file_exists(APP_PATH . 'bootstrap/cache/routes.php')) {
                $routes = require_once APP_PATH . 'bootstrap/cache/routes.php';
                $router->import($routes);

                if (isset($routes['apis']) && $routes['apis']) {
                    $di->get('apiHandler')->setApis($routes['apis']);
                }

                return $router;
            }

            $appConfig = $config->get('app.api', []);

            if (!isset($appConfig['enabled']) || $appConfig['enabled']) {
                $router->apiPrefix = $appConfig['prefix'] ?? $router->apiPrefix;
                $api = $router->api();
                $router->setHandler($di->get('apiHandler'));
            }

            require_once APP_PATH . 'app/routes.php';

            if (isset($api)) {
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
            if ($config->get('view.volt.enabled', false)) {
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

        if (!isset($voltConfig['enabled']) || ! $voltConfig['enabled']) {
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
            if (isset($voltConfig['functions']) && is_array($voltConfig['functions'])) {
                foreach ($voltConfig['functions'] as $functionName => $function) {
                    $compiler->addFunction($functionName, $function);
                }
            }

            return $volt;
        });
    }

    ///**
    // * @param DI $di
    // * @param Config $config
    // */
    //private function initDatabase(DI $di, Config $config): void
    //{
    //  /**
    //   * Set the database configuration
    //   */
    //  $this->registerDatabases($di, $config->get('database.log', false));
    //
    //  /**
    //   * If the configuration specify the use of metadata adapter use it or use memory otherwise
    //   */
    //  $di->setShared('modelsMetadata', function () {
    //      $metaData = new Files(array(
    //          'metaDataDir' => APP_PATH . 'storage/framework/cache/'
    //      ));
    //
    //      return $metaData;
    //  });
    //
    //  /**
    //   * Set the models cache service
    //   */
    //  $di->setShared('modelsCache', function () {
    //      // Cache data for one day by default
    //      $frontCache = new FrontendData(['lifetime' => 86400]);
    //
    //      $cache = new File($frontCache, array(
    //          'cacheDir' => APP_PATH . 'storage/framework/cache/'
    //      ));
    //
    //      return $cache;
    //  });
    //}

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

    ///**
    // * @return Config
    // */
    //private function initConfig(DI $di): Config
    //{
    //  $config = new Config();
    //  putenv('APP_VERSION=' . $config->get('app.version', '0.0.0'));
    //
    //  $timezone = $config->get('app.timezone');
    //  if ( $timezone ) {
    //      date_default_timezone_set($timezone);
    //  }
    //
    //  /**
    //   * Set config
    //   */
    //  $di->setShared('config', $config);
    //
    //  return $config;
    //}
}
