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

class Console
{
    use ApplicationTrait;

    public $argv;

    public function __construct($argv)
    {
        $this->argv = $argv;
    }

    public function start()
    {
        $this->setup();
        $this->setupConfig();

        define('APP_CLI', true);

        /**
         * TODO rollback migration from scaffold. how??
         */

        $app = new Application('envome', '0.1.0');

        $app->add((new \Phinx\Console\Command\Migrate())->setName('migrate'));
        $app->add((new \Phinx\Console\Command\Init())->setName('migration:init'));
        $app->add((new \Phinx\Console\Command\Rollback())->setName('migration:rollback'));
        $app->add((new \Phinx\Console\Command\Status())->setName('migration:status'));
        $app->add((new \Phinx\Console\Command\Create())->setName('migration:create'));

        $app->add(new \Envo\Console\Command\Down);
        $app->add(new \Envo\Console\Command\Up);
        $app->add(new \Envo\Console\Command\ClearStorage);
        $app->add(new \Envo\Console\Command\Scaffold);

        $app->run();
    }
}