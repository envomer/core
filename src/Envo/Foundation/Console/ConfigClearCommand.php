<?php

namespace Envo\Foundation\Console;

use Envo\Console\Command;
use Envo\Foundation\Event\DatabaseBackupGenerated;
use Envo\Support\System;
use Envo\Support\File;

class ConfigClearCommand extends Command
{
	/**
	 * The console command signature.
	 *
	 * @var string
	 */
	protected $signature = 'config:clear';
	
	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Clear cached configurations';

	public function handle()
	{
		$path = APP_PATH . 'bootstrap/cache/config.php';

		File::delete($path);

		$this->info('Configuration cache cleared!');
		
		return true;
	}
}