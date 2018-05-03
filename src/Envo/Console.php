<?php

namespace Envo;

use Envo\Database\Console\Migrate;
use Envo\Database\Console\MigrationCreate;
use Envo\Database\Console\MigrationReset;
use Envo\Database\Console\MigrationRollback;
use Envo\Database\Console\MigrationStatus;
use Envo\Foundation\ApplicationTrait;
use Envo\Foundation\Config;
use Envo\Foundation\Console\BackupCommand;
use Envo\Foundation\Console\ClearStorageCommand;
use Envo\Foundation\Console\DownCommand;
use Envo\Database\Console\MigrationScaffold;
use Envo\Foundation\Console\UpCommand;
use Envo\Queue\Console\WorkCommand;
use Envo\Support\Str;

use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Db\Adapter\Pdo\Sqlite;
use Phalcon\Di;
use Phalcon\DI\FactoryDefault;
use Symfony\Component\Console\Application;

class Console extends \Phalcon\Application
{
    use ApplicationTrait;
	
	/**
	 * @var array
	 */
    public $argv;
	
	/**
	 * Console constructor.
	 *
	 * @param array $argv
	 */
    public function __construct($argv)
    {
		$this->argv = $argv;
		parent::__construct();
    }
	
	/**
	 * Start the console
	 * @throws \Exception
	 */
    public function start()
    {
		//$di = new Di();
		$di = new FactoryDefault();
		
		/** Set config */
		$di->setShared('config', Config::class);
	
		$this->setDI($di);
    	$this->setup();
        $this->registerServices();
        $this->setupConfig();

        define('APP_CLI', true);
	
		if( isset($this->argv[1]) && Str::strposa($this->argv[1], ['migrate', 'queue']) ) {
			$this->registerDatabases($di);
		}

        $app = new Application('envome', '0.2.0');

        //$app->add((new SeedRun())->setName('seed'));

        $app->add(new DownCommand);
        $app->add(new UpCommand);
        $app->add(new ClearStorageCommand);
        $app->add(new MigrationScaffold);
        $app->add(new WorkCommand);
        $app->add(new BackupCommand);
        $app->add(new MigrationReset);
        $app->add(new Migrate);
		$app->add(new MigrationRollback);
		$app->add(new MigrationStatus);
		$app->add(new MigrationCreate);
		
        $app->run();
    }
	
	public function registerServices()
	{
		/**
		 * Register the module directories
		 */
		$loader = new \Phalcon\Loader();
		//$loader->registerDirs([ APP_PATH . 'library', APP_PATH . 'services']);
		$namespaces = [
			'Envo' => ENVO_PATH
		];
		$loader->registerNamespaces($namespaces);
		$loader->register();
	}
	
	/**
	 * Handles a request
	 */
	public function handle()
	{
		// TODO: Implement handle() method.
	}
	
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
		require_once 'Helper.php';
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
	}
}