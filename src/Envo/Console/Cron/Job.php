<?php

namespace Envo\Console\Cron;

class Job
{
	public $job;
	
	public $expression = '* * * * *';
	
	public function on($expression)
	{
		$this->expression = $expression;
	}
	
	public function yearly()
	{
		$this->expression = '@yearly';
	}
	
	public function monthly()
	{
		$this->expression = '@monthly';
	}
	
	public function weekly()
	{
		$this->expression = '@weekly';
	}
	
	public function daily()
	{
		$this->expression = '@daily';
	}
	
	public function hourly()
	{
		$this->expression = '@hourly';
	}
	
	public function minutely()
	{
		$this->expression = 'minutely';
	}
	
	public function every5Minutes()
	{
		$this->expression = '@5minutes';
	}
}