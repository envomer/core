<?php

namespace Envo\Command;

use Envo\Support\Queue;

class WorkerTask extends \Phalcon\Cli\Task
{
    public function mainAction()
    {
        echo "Running queue worker...\n";

        $modules = [];

        $queue = new Queue;

        while (true) {
            if( $jobs = $queue->getNextJobs(5) ) {
                echo "\n";
                foreach ($jobs as $i => $job) {
                    if( $i > 0 ) echo "\n";
                    echo 'Executing job (' . $job->type_name .')' . ". ";

                    $result = $queue->work($job);
                    if($result ) {
                        echo "Done. Deleted job. \n";
                    }
                    else echo "Done. Error: {$result}";
                }
                echo "\n";
            }

            sleep(5);
        }

        // Connect to the queue
        // See https://github.com/phalcon/incubator/blob/master/Library/Phalcon/Queue/Beanstalk/Extended.php
        // $queue = new \Queue();

        // $beanstalk = $queue->connect();

        // while(true) {
        //     while (($job = $beanstalk->peekReady()) !== false) {
        //         $body = $job->getBody();
        //         echo 'Executing job (' . $body['class'] .')' . ". ";
            
        //         $queue->work($body);

        //         $job->delete();
        //         echo "Done. Deleted job. \n";
        //     }

        //     sleep(5);
        // }
    }
}