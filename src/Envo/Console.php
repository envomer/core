<?php

namespace Envo;

use Envo\Console\Cron\RunCommand;
use Envo\Database\Console\Migrate;
use Envo\Database\Console\MigrationCreate;
use Envo\Database\Console\MigrationReset;
use Envo\Database\Console\MigrationRollback;
use Envo\Database\Console\MigrationStatus;
//use Envo\Foundation\ApplicationTrait;
use Envo\Foundation\Config;
use Envo\Foundation\Console\ConfigJsonCommand;
use Envo\Foundation\Console\ConfigClearCommand;
use Envo\Foundation\Console\ConfigCacheCommand;
use Envo\Foundation\Console\FuseStartCommand;
use Envo\Foundation\Console\MakeAPICommand;
use Envo\Foundation\Console\MakeControllerCommand;
use Envo\Foundation\Console\MakeDTOCommand;
use Envo\Foundation\Console\MakeEventCommand;
use Envo\Foundation\Console\MakeModelCommand;
use Envo\Foundation\Console\RouteCacheCommand;
use Envo\Foundation\Console\RouteClearCommand;
use Envo\Foundation\Console\BackupCommand;
use Envo\Foundation\Console\ClearStorageCommand;
use Envo\Foundation\Console\DownCommand;
use Envo\Database\Console\MigrationScaffold;
use Envo\Foundation\Console\UpCommand;
use Envo\Fuse\Console\InstallCommand;
use Envo\Fuse\Console\StartCommand;
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
	 * @var bool
	 */
    public $dbRegistered = false;

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
    	error_reporting(E_ALL);

    	$name = 'envome';
    	$version = '0.2.0';
		//$di = new Di();

        \define('APP_CLI', true);
        \define('ENVO_CLI', true);

        $inFuseMode = getenv('FUSE_CLI');
		if ($inFuseMode) {
			\define('FUSE_CLI', true);
			$name = 'Burning ' . $name;
			$version = '0.0.1';
		}

        $this->prepare();

        $app = new Application($name, $version);

        //$app->add((new SeedRun())->setName('seed'));
        $app->add(new ConfigJsonCommand);
        $app->add(new ConfigCacheCommand);
        $app->add(new ConfigClearCommand);
        $app->add(new RouteCacheCommand);
        $app->add(new RouteClearCommand);
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
		$app->add(new MakeAPICommand);
		$app->add(new MakeControllerCommand);
		$app->add(new MakeModelCommand);
		$app->add(new MakeEventCommand);
		$app->add(new MakeDTOCommand);
		$app->add(new RunCommand());

		if ($inFuseMode) {
			$app->add(new StartCommand);
			$app->add(new InstallCommand);
		}

		$this->registerAppCommands($app);

		$app->run();
    }

	/**
	 * @return void
	 */
	//public function registerServices($di, $config)
	//{
	//	/**
	//	 * Register the module directories
	//	 */
	//	//$loader = new \Phalcon\Loader();
	//	////$loader->registerDirs([ APP_PATH . 'library', APP_PATH . 'services']);
	//	//$namespaces = [
	//	//	'Envo' => ENVO_PATH
	//	//];
    //    //
	//	//$loader->registerNamespaces($namespaces);
	//	//$loader->register();
    //
	//	/**
	//	 * Custom authentication component
	//	 */
	//	$di->setShared('auth', Auth::class);
    //
	//	$di->setShared('crypt', function() use($config) {
	//		$crypt = new \Phalcon\Crypt();
	//		$crypt->setCipher($config->get('app.cipher'));
	//		$crypt->setKey($config->get('app.key'));
    //
	//		return $crypt;
	//	});
	//}

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
	//public function setup()
	//{
	//	\define('APP_START', microtime(true));
	//	\define('ENVO_PATH', __DIR__ . '/../');
    //
	//	if ( ! \defined('APP_PATH') ) {
	//		exit('APP_PATH not defined');
	//	}
    //
	//	/**
	//	 * Read configuration file
	//	 */
	//	if (! file_exists(APP_PATH . '.env') ) {
	//		throw new \Exception('Configuration file not set. Contact support team.', 500);
	//	}
    //
	//	ini_set('error_log', APP_PATH . 'storage/frameworks/logs/errors/'.date('Y-m.W').'.log');
    //
	//	// IP check
	//	require_once 'Helper.php';
	//}

	/**
	 * Setup .env configuration
	 */
	//public function setupConfig()
	//{
	//	$config = parse_ini_file(APP_PATH . '.env');
    //
	//	if ( getenv('APP_ENV') === 'testing' ) {
	//		unset($config['APP_ENV']);
	//	}
    //
	//	foreach($config as $key => $conf) {
	//		if ( \is_array($conf) ) {
	//			continue;
	//		}
    //
	//		putenv($key.'='.$conf);
	//	}
	//}

	/**
	 * Register database connections
	 *
	 * @param DI $di
	 * @param bool $debug
	 */
	//public function registerDatabases(DI $di = null, $debug = false)
	//{
	//	if ($this->dbRegistered) {
	//		return;
	//	}
    //
	//	if (!$di) {
	//		$di = Di::getDefault();
	//	}
    //
	//	$databaseConfig = config('database');
	//	$connections = ['db' => $databaseConfig['default']];
	//	if (isset($databaseConfig['use'])) {
	//		/** @var array $databaseConfig */
	//		foreach ($databaseConfig['use'] as $item){
	//			$connections[$item] = $item;
	//		}
	//	}
    //
	//	$self = $this;
	//	foreach ($connections as $key => $connectionName){
	//		$di->setShared($key, function () use($debug, $databaseConfig, $key, $connectionName, $self) {
	//			$data = $databaseConfig['connections'][$connectionName];
    //
	//			if ( $data['driver'] === 'sqlite' ) {
	//				$connection = new Sqlite($data);
	//			} else {
	//				$connection = new Mysql($data);
	//			}
    //
	//			if ( $debug ) {
	//				$connection->setEventsManager($self->dbDebug($key, $this));
	//			}
    //
	//			return $connection;
	//		});
	//	}
    //
	//	$this->dbRegistered = true;
	//}

	/**
	 * @param Application $app
	 *
	 * @throws \Exception
	 */
	public function registerAppCommands(Application $app)
	{
		if ( ! class_exists('Console') ) {
			return false;
		}

		$console = new \Console();

		if ( !method_exists($console, 'commands') ) {
			return false;
		}

		$commands = $console->commands();

		if (!$commands) {
			return false;
		}

		if ( !is_array($commands) ) {
			throw new \Exception('Console::commands() must return an array');
		}

		foreach ($commands as $command) {
			if (is_string(($command))) {
				$command = new $command();
			}

			$app->add($command);
		}

		return true;
	}

    public function prepare(): void
    {
        $di = new FactoryDefault();

        $di->setShared('app', $this);

        $this->setDI($di);

        $this->setup();
        $this->setupEnv();

        $config = $this->initConfig($di);

        $this->debug = $debug = env('APP_DEBUG');

        $this->registerBaseServices($config, $di);
        //$this->setupConfig();

        //if ( isset($this->argv[1]) && Str::strposa($this->argv[1], ['migrate', 'queue']) ) {
        $this->initDatabase($di, $config);
        //}
    }
}
