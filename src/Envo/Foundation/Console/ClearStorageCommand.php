<?php

namespace Envo\Foundation\Console;

use Envo\Console\Command;
use Envo\Support\File;

class ClearStorageCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'storage:clear
							{--errors : Clear errors}
							{--events : Clear events}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Clear the storage folder';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		$this->info('Clearing storage folders...');

		if( $this->option('errors', false) ) {
			$this->unlinkFolder('framework/logs/errors', '*.log');
			$this->line('Cleared errors.');
		}

		if( $this->option('events', false) ) {
			$this->unlinkFolder('framework/logs/events', '*.log');
			$this->line('Cleared events.');
		}

		File::delete(APP_PATH . 'bootstrap/cache/config.php');
		$this->line('Cleared cached config.');

		$this->unlinkFolder('framework/cache');
		$this->line('Cleared cache.');
		
		$this->unlinkFolder('framework/sessions');
		$this->line('Cleared sessions.');
		
		$this->unlinkFolder('framework/views');
		$this->line('Cleared views.');
	}

	public function unlinkFolder($path, $wildcard = '*')
	{
		$path = APP_PATH . 'storage/' . $path;
		$leave_files = array('.gitignore');

		foreach( glob("$path/$wildcard") as $file ) {
			if( !in_array(basename($file), $leave_files) ) {
				unlink($file);
			}
		}
	}
}