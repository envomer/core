<?php

namespace Envo\Queue\Connector;

use Envo\Queue\Job;

class MysqlConnector implements ConnectorInterface
{
    public function store($jobData, $delay = null, $queue = null)
    {
        // get job type
        // $jobType = QueueJobTypeRepository::getByName($data['class']);
        // if( ! $jobType ) {
        //     $jobType = new QueueJobType;
        //     $jobType->name = $data['class'];
        //     $jobType->created_at = \Date::now();
        //     $jobType->save();
        // }

        // $job = new \Core\Model\QueueJob();
        // $job->payload = json_encode($data);
        // $job->created_at = time();
        // $job->type_id = $jobType->id;
        // // $job->queue = 'normal';
        // $job->available_at = time() + $delay;
        // $job->attempts = 0;
        // $job->save();

        // return $job;
    }

    public function getNextJobs($limit = 5)
    {
        return [
            new Job([
                'payload' => [
                    'class' => 'Core\Controller\TestJob',
                ],
                'attempts' => 1,
                'available_at' => time(),
                'created_at' => time(),
                'status' => 1,
                'entity' => 'use Job model'
            ])
        ];
    }

    public function deleteJob(Job $job)
    {
        die(var_dump('delete job or at least mark job as done using $job->entity->save()'));
    }
}