<?php

namespace Envo;

use Envo\Foundation\ApplicationTrait;
use Envo\Library\Date;
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
        $app = new Application('envome', '0.0.1');

        $app->add((new \Phinx\Console\Command\Migrate())->setName('phinx:migrate'));
        $app->add((new \Phinx\Console\Command\Init())->setName('phinx:init'));
        $app->add((new \Phinx\Console\Command\Rollback())->setName('phinx:rollback'));
        $app->add((new \Phinx\Console\Command\Status())->setName('phinx:status'));
        $app->add((new \Phinx\Console\Command\Create())->setName('phinx:create'));

        $app->run();
    }
}