<?php

namespace Envo\Queue\Connector;

use Envo\Queue\Job;
use Envo\Queue\Model\QueueJob;
use Envo\Queue\Model\QueueJobType;
use Envo\Support\Arr;
use Envo\Support\Date;

class MysqlConnector implements ConnectorInterface
{
	/**
	 * @param $jobData
	 * @param integer $delay Seconds
	 * @param string $queue
	 *
	 * @return \Core\Model\QueueJob
	 */
    public function store($jobData, $delay = 0, $queue = null)
    {
    	$className = $jobData['class'] ?? null;
		
    	if(!$className || !class_exists($className)) {
    		internal_exception('app.classNotFound', 500);
		}
    	
        // get job type
         $jobType = QueueJobType::findFirstByClassNamespace($className);
         if( ! $jobType ) {
             $jobType = new QueueJobType();
             $jobType->class_namespace = $className;
             $jobType->created_at = Date::now();
             $jobType->status = QueueJobType::STATUS_ENABLED;
             $jobType->save();
         }

         $job = new QueueJob();
         $job->payload = json_encode($jobData);
         $job->created_at = time();
         $job->type_id = $jobType->id;
         $job->queue = $queue;
         $job->available_at = time() + $delay;
         $job->attempts = 0;
         $job->save();

         return $job;
    }
	
	/**
	 * @param int $limit
	 *
	 * @return array
	 */
    public function getNextJobs($limit = 5)
    {
		$query = QueueJob::repo()->createBuilder('q');
		$bind = [
			'available' => time(),
			'status_failed' => QueueJob::STATUS_FAILED,
			'status_enabled' => QueueJobType::STATUS_ENABLED
		];
		
		$where = 't.status = :status_enabled:';
		$where .= ' AND q.available_at < :available:';
		$where .= ' AND (q.done IS NULL OR q.done = 0)';
		$where .= ' AND (q.status != :status_failed: OR q.status IS NULL)';
		
		$columns = [
			'q.*',
			't.class_namespace as type_name',
		];
	
		$query->columns($columns);
		$query->join(QueueJobType::class, 'q.type_id = t.id', 't');
	
		$query->where($where, $bind);
		$query->limit($limit);
		
		$result = $query->getQuery()->execute();
	
		if( $result->count() ) {
			$combined = [];
			foreach ($result as $key => $value) {
				$combined[$key] = $value->q;
				if(isset($value->type_name)) {
					$combined[$key]->type_name = $value->type_name;
				}
				
				$combined[$key]->payload = json_decode($combined[$key]->payload, 1);
			}
			return Arr::toClass($combined, Job::class);
		}
		
		return null;
    }
	
	/**
	 * @param Job $job
	 *
	 * @return bool
	 */
    public function release(Job $job)
    {
    	/** @var QueueJob $queueJob */
    	$queueJob = QueueJob::findFirst($job->id);
    	if($queueJob) {
    		$queueJob->done = $job->done;
    		$queueJob->status = $job->status;
    		$queueJob->save();
		}
    	
    	return true;
    }
	
	/**
	 * @param Job $job
	 * @param \Exception $exception
	 *
	 * @return bool
	 */
    public function retryJob(Job $job, \Exception $exception)
	{
		/** @var QueueJob $queueJob */
		$queueJob = QueueJob::findFirst($job->id);
		
		$queueJob->failed_at = $job->failed_at;
		$queueJob->exception = $job->exception;
		$queueJob->status = $job->status;
		
		$queueJob->save();
		
		return true;
	}
	
}