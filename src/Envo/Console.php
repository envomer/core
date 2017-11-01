<?php

namespace Envo;

use Envo\Foundation\ApplicationTrait;
use Envo\Foundation\Config;
use Envo\Support\Date;
use Phalcon\Commands\CommandsListener;
use Phalcon\Loader;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Exception as PhalconException;
use Phalcon\Di\FactoryDefault\Cli as CliDI;
use Phalcon\Cli\Console as ConsoleApp;
use Phalcon\Db\Adapter\Pdo\Mysql as Database;
use Symfony\Component\Console\Application;

class Console extends \Phalcon\Cli\Console
{
    use ApplicationTrait;

    public $argv;

    public function __construct($argv)
    {
        $this->argv = $argv;
    }
	
	/**
	 * Start the console
	 * @throws \Exception
	 */
    public function start()
    {
        $this->setup();
        $this->setupConfig();
        $this->setDI(new \Phalcon\DI\FactoryDefault);
        $di = $this->getDI();
	
		/**
		 * Set config
		 */
		$di->setShared('config', Config::class);

        define('APP_CLI', true);

        if( isset($this->argv[1]) && strpos($this->argv[1], 'migrate:') === 0  ) {
            define('ENVO_INCLUDE_MIGRATIONS', true);
        }
	
		if( isset($this->argv[1]) && strpos($this->argv[1], 'migrate') === 0  ) {
			$this->registerDatabases($di);
		}

        $app = new Application('envome', '0.2.0');

        //$app->add((new \Phinx\Console\Command\Migrate())->setName('migrate'));
        $app->add((new \Phinx\Console\Command\Init())->setName('migrate:init'));
        $app->add((new \Phinx\Console\Command\Rollback())->setName('migrate:rollback'));
        $app->add((new \Phinx\Console\Command\Status())->setName('migrate:status'));
        $app->add((new \Phinx\Console\Command\Create())->setName('make:migration'));
        $app->add((new \Phinx\Console\Command\SeedCreate())->setName('make:seeder'));
        $app->add((new \Phinx\Console\Command\SeedRun())->setName('seed'));

        $app->add(new \Envo\Foundation\Console\DownCommand);
        $app->add(new \Envo\Foundation\Console\UpCommand);
        $app->add(new \Envo\Foundation\Console\ClearStorageCommand);
        $app->add(new \Envo\Foundation\Console\ScaffoldCommand);
        $app->add(new \Envo\Queue\Console\WorkCommand);
        $app->add(new \Envo\Foundation\Console\BackupGeneratorCommand);
        $app->add(new \Envo\Database\Console\MigrationReset);
        $app->add(new \Envo\Database\Console\Migrate);
        // $app->add(new \Envo\Database\Console\MigrationRollback);
        // $app->add(new \Envo\Database\Console\MigrationStatus);

        $app->run();
    }
}