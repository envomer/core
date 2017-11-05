<?php

namespace Envo\Database\Console;

use Envo\Console\Command;
use Envo\Database\Migration\Manager;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class BaseCommand extends Command
{
	/**
	 * @var Manager
	 */
	protected $manager;
	
	/**
	 * @param InputInterface  $input
	 * @param OutputInterface $output
	 *
	 * @return int|null|void
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->manager = new Manager();
		$this->manager->command = $this->name;
		
		parent::execute($input, $output);
	}
	
	/**
	 * Get migration path
	 *
	 * @return mixed|null|string
	 */
	public function getMigrationPath()
	{
		$path = $this->option('path');
		
		if(!$path) {
			$path = APP_PATH . 'resources/database/migrations';
		}
		
		return $path;
	}
	
}