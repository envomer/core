<?php

namespace Envo\Fuse\Console;

use Envo\Console\Command;

class StartCommand extends Command
{
	/**
	 * The console command signature.
	 *
	 * @var string
	 */
	protected $signature = 'start';
	
	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Ignite the fire';
	
	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function handle()
	{
		$this->comment('Igniting...');
	}
}