<?php

namespace Envo\Console\Command;

use Envo\Console\Command;

class ClearStorage extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'storage:clear {--logs}';

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
	public function fire()
	{
		$this->info('Clearing storage folders...');

		if( $this->option('logs', false) ) {
			$this->unlinkFolder('logs', '*.log');
			$this->line('Cleared logs.');
		}

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