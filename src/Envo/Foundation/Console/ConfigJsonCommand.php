<?php

namespace Envo\Foundation\Console;

use Envo\Console\Command;
use Envo\Foundation\Event\DatabaseBackupGenerated;
use Envo\Support\System;

class ConfigJsonCommand extends Command
{
	/**
	 * The console command signature.
	 *
	 * @var string
	 */
	protected $signature = 'config:json {name? : The name of the config file.}';
	
	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Return config as JSON';

    public function handle()
	{
		$name = trim($this->input->getArgument('name')) ?: null;

		$configuration = null;
		if(!$name) {
			$configuration = config()->all();
		} else {
			$configuration = config($name);
		}

		if(!$configuration) {
			$this->warn('No such file found: ' . $name);

			return true;
		}


		echo json_encode($configuration) . PHP_EOL;
		
		return true;
	}
}