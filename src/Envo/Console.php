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

        $this->listen();
    }

    public function listen()
    {
        $di = new CliDI();

        /**
         * Set the database configuration
         */
        $di->set('db', function () use ($di) {
            $databaseConfig = require(APP_PATH . 'config/database.php');
            $connection = new Database($databaseConfig['connections'][$databaseConfig['default']]);

            return $connection;
        });

        // Create a console application
        $console = new ConsoleApp();
        $console->setDI($di);

        /**
         * Process the console arguments
         */
        $arguments = [
            'task' => isset($this->argv[1]) ? ucfirst($this->argv[1]) : 'Main',
            'action' => isset($this->argv[2]) ? $this->argv[1] : null,
            'params' => isset($this->argv[3]) ? $this->argv[1] : null,
        ];

        $arguments['task'] = 'Envo\Command\\' . ucfirst($arguments['task']);

        // Define global constants for the current task and action
        define('CURRENT_TASK',   (isset($this->argv[1]) ? $this->argv[1] : null));
        define('CURRENT_ACTION', (isset($this->argv[2]) ? $this->argv[2] : null));

        $vendor = sprintf('Envo (%s)', env('APP_VERSION'));
        print $vendor ."\n";
        print "Date: " . Date::now() . PHP_EOL . PHP_EOL;

        try {
            // Handle incoming arguments
            $console->handle($arguments);
        } catch (\Exception $e) {
            echo $e->getMessage();

            die(var_dump($e));
            exit(255);
        }
    }
}