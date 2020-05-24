<?php

namespace Envo;

use Envo\API\Handler;
use Envo\Foundation\Cache;
use Envo\Foundation\Config;
use Envo\Foundation\ExceptionHandler;
use Envo\Foundation\Permission;
use Envo\Support\Str;
use Envo\Support\Translator;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Db\Adapter\Pdo\Sqlite;
use Phalcon\Db\Profiler;
use Phalcon\Di;
use Phalcon\Escaper;
use Phalcon\Events\Event;
use Phalcon\Events\Manager;
use Phalcon\Events\Manager as EventManager;
use Phalcon\Http\Request;
use Phalcon\Http\Response;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Manager as ModelManager;
use Phalcon\Mvc\Model\MetaData\Stream;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Url;

trait ApplicationTrait
{
    private $debug;

    /**
     * @param Config $config
     * @param Di $di
     */
    public function registerBaseServices(Config $config, $di)
    {
        $di->setShared('crypt', function() use($config) {
            $crypt = new \Phalcon\Crypt();
            $crypt->setCipher($config->get('app.cipher'));
            $crypt->setKey($config->get('app.key'));

            return $crypt;
        });

        /**
         * Set permission
         */
        if ($config->get('app.permissions.enabled')) {
            $di->setShared('permission', Permission::class);
        }

        /**
         * Set models manager
         */
        $di->setShared('modelsManager', ModelManager::class);

        /**
         * Set request
         */
        $di->setShared('request', Request::class);

        /**
         * Set caching
         */
        $di->setShared('cache', Cache::class);

        /**
         * Set response
         */
        $di->setShared('response', Response::class);

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
        $di->setShared('eventsManager', function() {
            $eventManager = new Manager();
            $eventManager->attach('dispatch:beforeException', new ExceptionHandler);

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
         * Set URL component
         */
        $url = new \Phalcon\Url();
        $url->setBaseUri('/');
        $di->setShared('url', $url);

        if ( $this->debug ) {
            $di->set('escaper', Escaper::class, true);
            $di->set('profiler', Profiler::class, true);

            //$debug = new \Phalcon\Debug();
            //$debug->listen();
        }
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

        ini_set('error_log', APP_PATH . 'storage/framework/logs/errors/'.date('Y-m.W').'.log');

        if (!defined('ENVO_PATH')) {
            define('ENVO_PATH', __DIR__ . '/../');
        }

        if ( ! defined('APP_PATH') ) {
            throw new \Exception('app.appPathNotDefined', 500);
        }

        /**
         * Read configuration file
         */
        if (! file_exists(APP_PATH . '.env') ) {
            throw new \Exception('app.envConfigurationFileNotFound', 500);
        }
    }

    /**
     * Setup .env configuration
     */
    public function setupEnv()
    {
        $config = parse_ini_file(APP_PATH . '.env');

        if ( getenv('APP_ENV') === 'testing' ) {
            unset($config['APP_ENV']);
        }

        if (defined('APP_TESTING') && APP_TESTING && file_exists(APP_PATH . '.env.test')) {
            $configTesting = parse_ini_file(APP_PATH . '.env.test');

            if ($configTesting) {
                $config = array_merge($config, $configTesting);
            }
        }

        foreach($config as $key => $conf) {
            if (substr($key, 0, 1) === '#') {
                continue;
            }
            if ( is_array($conf) ) {
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
        if (!$this->debug) {
            return;
        }

        // log the mysql queries if APP_DEBUG is set to true
        /** @var Profiler $profiler */
        $profiler = $di->get('profiler');
        $eventsManager = new EventManager();
        $eventsManager->collectResponses(true);

        // Listen all the database events
        //$requestDebug = isset($_GET['cc2']); // add this to config or so...
        $adapter = new \Phalcon\Logger\Adapter\Stream( APP_PATH . 'storage/framework/logs/db/db-'.date('Y-m-d').'.log');
        $logger  = new \Phalcon\Logger(
            'db',
            [
                'main' => $adapter,
            ]
        );
    
        $eventsManager->attach($databaseName, function(Event $event, $connection) use ($profiler, $logger) {
            if ($event->getType() === 'beforeQuery') {
                // $traces = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
                $profiler->startProfile($connection->getSQLStatement());
                $item = $profiler->getLastProfile();
                $item->setSqlVariables($connection->getSqlVariables() ?: []);
                $item->setSqlBindTypes($connection->getSqlBindTypes() ?: []);

                //if ($requestDebug) {
                $ignoreClasses = ['Phalcon\\', 'Application'];
                $path = '';
                foreach(debug_backtrace() as $trace) {
                    if ( isset($trace['class']) && Str::strposa($trace['class'], $ignoreClasses) ){
                        continue;
                    }
                    $path .= ($trace['class'] ?? '') . '::' . $trace['function'].';';
                }
                //var_dump($connection->getSQLStatement(), $connection->getSQLVariables());
                //echo "Execution Time: {$profiler->getTotalElapsedSeconds()}. <br> PATH: {$path}\n\r";
                //echo '<br><br>-----------------------------------------------------------------------<br><br>';
                $logger->debug($connection->getSQLStatement() . " [Execution Time: {$profiler->getTotalElapsedSeconds()}. PATH: {$path}]\n\r");
                //}
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
    public function registerDatabases(DI $di, Config $config)
    {
        $logging =  $config->get('database.log', false);

        $databaseConfig = $config->get('database');

        $connections = ['db' => $databaseConfig['default']];
        if (isset($databaseConfig['use'])) {
            /** @var array $databaseConfig */
            foreach ($databaseConfig['use'] as $item){
                $connections[$item] = $item;
            }
        }

        $self = $this;
        foreach ($connections as $key => $connectionName){
            $di->setShared($key, function () use($logging, $databaseConfig, $key, $connectionName, $self) {
                $data = $databaseConfig['connections'][$connectionName];

                if ( $data['driver'] === 'sqlite' ) {
                    $connection = new Sqlite($data);
                } else {
                    $connection = new Mysql($data);
                }

                if ( $logging && $self->debug ) {
                    $connection->setEventsManager($self->dbDebug($key, $this));
                }

                return $connection;
            });
        }

        // @see https://docs.phalconphp.com/en/3.2/db-models#disabling-enabling-features
        $dbConfig = config('database');

        Model::setup([
            'astCache'              => $dbConfig['astCache'] ?? null,
            'cacheLevel'            => $dbConfig['cacheLevel'] ?? 3,
            'castOnHydrate'         => $dbConfig['castOnHydrate'] ?? false,
            'columnRenaming'        => $dbConfig['columnRenaming'] ?? true,
            'disableAssignSetters'  => $dbConfig['disableAssignSetters'] ?? false,
            'enableImplicitJoins'   => $dbConfig['enableImplicitJoins'] ?? true,
            'enableLiterals'        => $dbConfig['enableLiterals'] ?? true,
            'escapeIdentifiers'     => $dbConfig['escapeIdentifiers'] ?? true,
            'events'                => $dbConfig['events'] ?? true,
            'exceptionOnFailedSave' => $dbConfig['exceptionOnFailedSave'] ?? true,
            'forceCasting'          => $dbConfig['forceCasting'] ?? false,
            'ignoreUnknownColumns'  => $dbConfig['ignoreUnknownColumns'] ?? false,
            'lateStateBinding'      => $dbConfig['lateStateBinding'] ?? false,
            'notNullValidations'    => $dbConfig['notNullValidations'] ?? true,
            'parserCache'           => $dbConfig['parserCache'] ?? null,
            'phqlLiterals'          => $dbConfig['phqlLiterals'] ?? true,
            'uniqueCacheId'         => $dbConfig['uniqueCacheId'] ?? 3,
            'updateSnapshotOnSave'  => $dbConfig['updateSnapshotOnSave'] ?? true,
            'virtualForeignKeys'    => $dbConfig['virtualForeignKeys'] ?? true,
        ]);
    }

    /**
     * @param DI $di
     * @param Config $config
     */
    private function initDatabase(DI $di, Config $config): void
    {
        /**
         * Set the database configuration
         */
        $this->registerDatabases($di, $config);

        /**
         * If the configuration specify the use of metadata adapter use it or use memory otherwise
         */
        $di->setShared('modelsMetadata', function () {
            $metaData = new Stream(array(
                'metaDataDir' => APP_PATH . 'storage/framework/cache/'
            ));

            return $metaData;
        });

        /**
         * Set the models cache service
         */
        $di->setShared('modelsCache', function () {
            // Cache data for one day by default
            $serializerFactory = new SerializerFactory();

            $cache = new \Phalcon\Cache\Adapter\Stream($serializerFactory,
                [
                    'defaultSerializer' => 'Php',
                    'lifetime' => 86400,
                    'storageDir' => APP_PATH . 'storage/framework/cache/'
                ]
            );

            return $cache;
        });
    }

    /**
     * @return Config
     */
    private function initConfig(DI $di): Config
    {
        $config = new Config();
        putenv('APP_VERSION=' . $config->get('app.version', '0.0.0'));

        if ( getenv('APP_ENV') === 'testing' ) {
            unset($config['APP_ENV']);
        }

        $timezone = $config->get('app.timezone');
        if ( $timezone ) {
            date_default_timezone_set($timezone);
        }

        /**
         * Set config
         */
        $di->setShared('config', $config);

        return $config;
    }
}
