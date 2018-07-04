<?php

namespace Envo\Foundation\Console;

use Envo\Console\Command;
use Envo\Support\File;

class FuseStartCommand extends Command
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
	protected $description = 'Ignite the fire mode';
	
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