<?php

namespace Envo\Queue\Connector;

use Envo\AbstractJob;
use Envo\Queue\Job;

interface ConnectorInterface
{
    // public function push();
    // public function connect();
    // public function work($dataData);
    // public function sleep(AbstractJob $job);
    // public function wakeUp($jobData);

    public function store($jobData, $delay = null, $queue = null);

    public function getNextJobs($limit = 5);

    public function release(Job $job);
}