<?php

namespace Envo\Fuse\Console;

use Envo\Console\Command;

class InstallCommand extends Command
{
	/**
	 * The console command signature.
	 *
	 * @var string
	 */
	protected $signature = 'install';
	
	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Install php extension';
	
	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function handle()
	{
		$this->comment('Install...');
	}
}