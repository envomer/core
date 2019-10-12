<?php

namespace Envo\Console\Cron;

use Ahc\Cron\Expression;
use Envo\Console\Command;

class RunCommand extends Command
{
	/**
	 * The console command signature.
	 *
	 * @var string
	 */
	protected $signature = 'cron:run';
	
	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Run cron jobs';
	
	/**
	 * @return mixed|void
	 */
	public function handle()
	{
		$this->info('Running cron jobs...');
		
		$cron = $this->findJobs();
		if (!$cron) {
			return;
		}
		
		$jobs = $this->jobsDue($cron->getJobs());
		if (!$jobs) {
			$this->error('No jobs due!');
			return;
		}
		
		$this->runJobs($jobs);
	}
	
	/**
	 * @param array $jobs
	 */
	public function runJobs(array $jobs)
	{
		$this->line('');
		foreach ($jobs as $expression => $job) {
			$this->runJob($job);
		}
		$this->line('');
	}
	
	/**
	 * @param Job $job
	 */
	public function runJob(Job $job)
	{
		$className = is_string($job->job) ? $job->job : get_class($job->job);
		
		$this->line('Cron ' . $className . ': <info>Start...</info>' );
		
		$instance = new $className();
		
		if($instance instanceof Command) {
			$instance->output = $this->output;
			$instance->handle();
		}
	}
	
	/**
	 * @param array $jobs
	 *
	 * @return array
	 */
	public function jobsDue(array $jobs)
	{
		$due = [];
		
		foreach ($jobs as $job) {
			if(Expression::isDue($job->expression)) {
				$due[] = $job;
			}
		}
		
		return $due;
	}
	
	/**
	 * @return \Envo\Console\Cron|null
	 */
	public function findJobs()
	{
		if (!class_exists('Console')) {
			$this->error('\Console class not found.');
			return null;
		}
		
		$console = new \Console();
		
		if (!method_exists($console, 'cronjobs')) {
			$this->error('Console:cronjobs method not found.');
			return null;
		}
		
		return $console->cronJobs();
	}
}