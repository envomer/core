<?php

namespace Envo;

use Envo\Queue\Manager;

abstract class AbstractJob
{
	/**
	 * This method contains the logic that is triggered once the job
	 * is processed
	 * @return boolean
	 */
    abstract public function handle();
	
	
	/**
	 * @param int $delay Seconds
	 * @param string $queue
	 */
    public function push($delay = null, $queue = null)
	{
		/** @var Manager $manager */
		$manager = resolve('queue', Manager::class);
		
		$manager->push($this);
	}
}