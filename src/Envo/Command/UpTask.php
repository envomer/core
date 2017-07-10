<?php

namespace Envo\Command;

use Envo\Console\Command;

class UpTask extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'up';

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
    public function fire()
    {
        @unlink(APP_PATH.'storage/framework/down');

        $this->info('Application is now live.');
    }
}