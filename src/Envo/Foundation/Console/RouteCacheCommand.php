<?php

namespace Envo\Foundation\Console;

use Envo\Console\Command;
use Envo\Foundation\Event\DatabaseBackupGenerated;
use Envo\Support\System;
use Envo\Support\File;

class RouteCacheCommand extends Command
{
	/**
	 * The console command signature.
	 *
	 * @var string
	 */
	protected $signature = 'route:cache';
	
	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Cache routes';

	public function handle()
	{
		$this->warn('Coming soon');
		return true;

		$path = APP_PATH . 'bootstrap/cache/routes.php';

		File::delete($path);
		
		$application = new \Envo\Application();
		$application->initialize();

		$router = $application->di->get('router');

		$routes = $router->getRoutes();
		$data = [];
		foreach ($routes as $key => $route) {
			// die(var_dump($route));
			$data[] = $route;
		}

		// die(var_dump($data));

		$data = base64_encode(serialize($data));

		File::put($path, "<?php\n return unserialize(base64_decode('".$data."'));" . PHP_EOL);

		$this->info('Configuration cached successfully!');
		
		return true;
	}
}