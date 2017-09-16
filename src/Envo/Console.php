<?php

namespace Envo;

use Envo\Foundation\ApplicationTrait;
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
     */
    public function start()
    {
        $this->setup();
        $this->setupConfig();
        // $this->registerServices();
        $this->setDi(new \Phalcon\DI\FactoryDefault);

        define('APP_CLI', true);

        /**
         * TODO rollback migration from scaffold. how??
         */
        if( isset($this->argv[1]) && $this->argv[1] === 'migration:rollback' ) {
            define('ENVO_INCLUDE_MIGRATIONS', true);
        }

        $app = new Application('envome', '0.1.1');

        $app->add((new \Phinx\Console\Command\Migrate())->setName('migrate'));
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

        $app->run();
    }
}