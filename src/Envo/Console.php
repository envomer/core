<?php

namespace Envo;

use Envo\Database\Console\Migrate;
use Envo\Database\Console\MigrationReset;
use Envo\Database\Console\MigrationRollback;
use Envo\Database\Console\MigrationStatus;
use Envo\Foundation\ApplicationTrait;
use Envo\Foundation\Config;
use Envo\Foundation\Console\BackupGeneratorCommand;
use Envo\Foundation\Console\ClearStorageCommand;
use Envo\Foundation\Console\DownCommand;
use Envo\Database\Console\MigrationScaffold;
use Envo\Foundation\Console\UpCommand;
use Envo\Queue\Console\WorkCommand;

use Phalcon\DI\FactoryDefault;
//use Phinx\Console\Command\Create;
//use Phinx\Console\Command\Init;
//use Phinx\Console\Command\SeedCreate;
//use Phinx\Console\Command\SeedRun;
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
        $this->setup();
        $this->setupConfig();
        $this->setDI(new FactoryDefault);
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
        //$app->add((new Init())->setName('migrate:init'));
        //$app->add((new \Phinx\Console\Command\Rollback())->setName('migrate:rollback'));
        //$app->add((new \Phinx\Console\Command\Status())->setName('migrate:status'));
        //$app->add((new Create())->setName('make:migration'));
        //$app->add((new SeedCreate())->setName('make:seeder'));
        //$app->add((new SeedRun())->setName('seed'));

        $app->add(new DownCommand);
        $app->add(new UpCommand);
        $app->add(new ClearStorageCommand);
        $app->add(new MigrationScaffold);
        $app->add(new WorkCommand);
        $app->add(new BackupGeneratorCommand);
        $app->add(new MigrationReset);
        $app->add(new Migrate);
		$app->add(new MigrationRollback);
		$app->add(new MigrationStatus);

        $app->run();
    }
	
	/**
	 * Handles a request
	 */
	public function handle()
	{
		// TODO: Implement handle() method.
	}
}