<?php

namespace Envo\Queue\Console;

use Envo\Console\Command;
use Envo\Queue;
use Envo\Support\Date;

class WorkCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'queue:work
                            {connection? : The name of the queue connection to work}
                            {--queue= : The names of the queues to work}
                            {--once : Only process the next job on the queue}
                            {--delay=0 : Amount of time to delay failed jobs}
                            {--force : Force the worker to run even in maintenance mode}
                            {--memory=128 : The memory limit in megabytes}
                            {--sleep=3 : Number of seconds to sleep when no job is available}
                            {--timeout=60 : The number of seconds a child process can run}
                            {--tries=0 : Number of times to attempt a job before logging it failed}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start processing jobs on the queue as a daemon';

    public function handle()
    {
        echo "Running queue worker...\n";

        $queue = new Queue();

        while (true) {
            if( $jobs = $queue->getNextJobs(5) ) {
                foreach ($jobs as $i => $job) {
					$this->line(Date::now() . ' ' . $job->type_name . ' (' . $job->id . ') : <comment>Executing...</comment>');

                    $result = $queue->run($job);
                    if(is_bool($result) && $result) {
						$this->line(Date::now() . ' ' . $job->type_name . ' (' . $job->id . ') : <info>Finished and released.</info>');
                    } else {
						$this->line(Date::now() . ' ' . $job->type_name . ' (' . $job->id . ') : <fg=red>Failed ('.$result.')</>');
                    }
                }
            }

            sleep(5);
        }
    }
}