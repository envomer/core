<?php

namespace Envo\Foundation\Console;

use Envo\Console\Command;
use Envo\Foundation\Event\DatabaseBackupGenerated;
use Envo\Support\System;
use Envo\Support\File;

class RouteClearCommand extends Command
{
	/**
	 * The console command signature.
	 *
	 * @var string
	 */
	protected $signature = 'route:clear';
	
	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Clear cached routes';

	public function handle()
	{
		$path = APP_PATH . 'bootstrap/cache/routes.php';

		File::delete($path);

		$this->info('Cached routes cleared!');
		
		return true;
	}
}