<?php

namespace Envo\Foundation\Console;

use Envo\Console\Command;
use Envo\Foundation\Event\DatabaseBackupGenerated;
use Envo\Support\System;
use Envo\Support\File;

class ConfigCacheCommand extends Command
{
	/**
	 * The console command signature.
	 *
	 * @var string
	 */
	protected $signature = 'config:cache';
	
	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Cache configurations';

	public function handle()
	{
		$path = APP_PATH . 'bootstrap/cache/config.php';

		File::delete($path);

		$configuration = config()->all(true);

		File::put($path, '<?php return '.var_export($configuration, true).';'.PHP_EOL);

		$this->info('Configuration cached successfully!');
		
		return true;
	}
}