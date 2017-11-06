<?php

namespace Envo\Foundation\Console;

use Envo\Console\Command;

class UpCommand extends Command
{
	/**
	 * The console command signature.
	 *
	 * @var string
	 */
	protected $signature = 'up';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bring the application out of maintenance mode';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        @unlink(APP_PATH.'storage/framework/down');

        $this->info('Application is now live.');
    }
}