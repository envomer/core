<?php

namespace Envo\Console;

class Command extends \Phalcon\Cli\Task
{
    public function comment($message)
    {
        echo $message . PHP_EOL;
    }

    public function info($message)
    {
        echo $message . PHP_EOL;
    }

    public function mainAction()
    {
        $this->fire();
    }

    public function option()
    {
        return null;
    }
}