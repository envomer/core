<?php

namespace Envo;

use Envo\Queue\Manager;
use Envo\Queue\Job;

class Queue
{
    protected $manager = null;

    public function push(AbstractJob $job, $delay = 0)
    {
        $manager = $this->getManager();

        $manager->push($job, $delay);
    }

    public function getManager()
    {
        if( ! $this->manager ) {
            $this->manager = new Manager();
        }

        return $this->manager;
    }

    public function getNextJobs($limit = 5)
    {
        return $this->manager->getNextJobs($limit);
    }

    public function run(Job $job)
    {
        return $this->manager->work($job);
    }
}