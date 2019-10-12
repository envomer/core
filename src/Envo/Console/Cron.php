<?php

namespace Envo\Console;

use Envo\Console\Cron\Job;

class Cron
{
	/**
	 * @var Job[]
	 */
	protected $jobs = [];
	
	/**
	 * @param $name
	 *
	 * @return Job
	 */
	public function add($name) : Job
	{
		$job = new Job();
		$job->job = $name;
		
		$this->jobs[] = $job;
		
		return $job;
	}
	
	public function getJobs()
	{
		return $this->jobs;
	}
}