<?php

namespace Envo;

use Envo\Queue\Manager;
use Envo\Queue\Job;

class Queue
{
	/**
	 * @var Manager
	 */
    protected $manager;
	
	/**
	 * @param AbstractJob $job
	 * @param int $delay
	 *
	 * @throws Exception\InternalException
	 * @throws \ReflectionException
	 */
    public function push(AbstractJob $job, $delay = 0)
    {
        $manager = $this->getManager();

        $manager->push($job, $delay);
    }
	
	/**
	 * @return Manager
	 * @throws Exception\InternalException
	 */
    public function getManager(): Manager
	{
        if( ! $this->manager ) {
            $this->manager = new Manager();
        }

        return $this->manager;
    }
	
	/**
	 * @param int $limit
	 *
	 * @return Job[]
	 * @throws Exception\InternalException
	 */
	public function getNextJobs($limit = 5): array
	{
        return $this->getManager()->getNextJobs($limit);
    }
	
	/**
	 * @param Job $job
	 *
	 * @return bool|string|null
	 * @throws Exception\InternalException
	 * @throws \ReflectionException
	 */
    public function run(Job $job)
    {
        return $this->getManager()->work($job);
    }
}