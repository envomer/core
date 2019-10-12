<?php

namespace Envo\Support;

use Core\Model\QueueJob;
use Core\Model\QueueJobType;
use Core\Model\QueueJobRepository;
use Core\Model\QueueJobTypeRepository;

class Queue
{
	protected static $queue = null;
	protected static $beanstalk = null;

	public static function push($job, $delay = 0)
	{
		if ( ! self::$queue ) {
            self::$queue = new self();
        }
		$queue = self::$queue;

		if ( ! self::$beanstalk ) {
            self::$beanstalk = $queue->connect();
        }
		$beanstalk = self::$beanstalk;

		$data = $queue->sleep($job);
        
        $queueJob = $queue->storeJob($data, $delay);

        $data['id'] = $queueJob->id;

  //       try {
		// 	$beanstalk->put($data);
		// }
		// catch(Exception $e) {

  //           try {
  //               $job->handle();
  //           }
  //           catch (\Exception $ex) {
  //               $queueJob->attempts += 1;
  //               $queueJob->failed_at = time();
  //               $queueJob->exception = $ex->getMessage();
  //               $queueJob->save();

  //               throw $ex;
  //           }

  //           if ( env('APP_ENV') == 'production' ) {
  //               $message = 'Beanstalk exception: ' . $e->getMessage();
  //               \Notification::pushoverRemind($_SERVER['SERVER_NAME'],  'IP: ' . \IP::getIpAddress(). ' ' . "\n\rMessage: " . $message, 60*5);
  //           }

  //           return false;
		// }

  //       $queueJob->done = 1;
  //       $queueJob->save();

		return true;
	}

    public function storeJob($data, $delay = 0)
    {
        // get job type
        $jobType = QueueJobTypeRepository::getByName($data['class']);
        if ( ! $jobType ) {
            $jobType = new QueueJobType;
            $jobType->name = $data['class'];
            $jobType->created_at = \Date::now();
            $jobType->save();
        }

        $job = new \Core\Model\QueueJob();
        $job->payload = json_encode($data);
        $job->created_at = time();
        $job->type_id = $jobType->id;
        // $job->queue = 'normal';
        $job->available_at = time() + $delay;
        $job->attempts = 0;
        $job->save();

        return $job;
    }

    public function getNextJobs($limit = 5)
    {
        $jobs = QueueJobRepository::getNextJobs($limit);
        return $jobs ? $jobs : null;
    }

	public function connect()
	{
		return new \Phalcon\Queue\Beanstalk(
		    array(
		        'host' => '127.0.0.1',
		        'port' => '11300'
		    )
		);
	}

	public function work($data)
	{
        if ( is_object($data) ) {
            $job = $data;
            $data = json_decode($job->payload, true);
        }
        else {
            $jobId = $data['id'];
            $job = \Core\Model\QueueJobRepository::getByIdWithType($jobId);
        }

		$class = $this->wakeup($data);

        $job->attempts += 1;

        $result = null;

        try {
            $result = $class->handle();
        }
        catch (\Exception $e) {

            $result = $e->getMessage(). "\n"
             . " Class=" . get_class($e) . "\n"
             . " File=". $e->getFile(). "\n"
             . " Line=". $e->getLine(). "\n"
             . $e->getTraceAsString() . "\n";

            $job->failed_at = time();
            $job->exception = $result;
            $job->status = $job::STATUS_FAILED;
            $job->save();

            // throw $e;
        }

        $job->done = 1;
        $job->save();

        return $result;
        // return $class->handle();
	}

	/**
     * Prepare the instance for serialization.
     *
     * @return array
     */
    protected function sleep($class)
    {
    	$reflector = new \ReflectionClass($class);
        $properties = $reflector->getProperties();

        return array(
        	'class' => get_class($class),
        	'properties' => (array)$class
        );
    }

    /**
     * Restore the model after serialization.
     *
     * @return void
     */
    protected function wakeup($data)
    {
        $class = $data['class'];
        $parameters = $data['properties'];

        $reflector = new \ReflectionClass($class);

        $constructor = $reflector->getConstructor();
        $dependencies = $constructor->getParameters();

        // Once we have all the constructor's parameters we can create each of the
        // dependency instances and then use the reflection instances to make a
        // new instance of this class, injecting the created dependencies in.
        $parameters = $this->keyParametersByArgument(
            $dependencies, $parameters
        );

        $instances = $this->getDependencies(
            $dependencies, $parameters
        );

        return $reflector->newInstanceArgs($instances);
    }

    /**
     * Resolve all of the dependencies from the ReflectionParameters.
     *
     * @param  array  $parameters
     * @param  array  $primitives
     * @return array
     */
    protected function getDependencies(array $parameters, array $primitives = [])
    {
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $dependency = $parameter->getClass();

            // If the class is null, it means the dependency is a string or some other
            // primitive type which we can not resolve since it is not a class and
            // we will just bomb out with an error since we have no-where to go.
            if (array_key_exists($parameter->name, $primitives)) {
                $dependencies[] = $primitives[$parameter->name];
            } elseif (is_null($dependency)) {
                $dependencies[] = $this->resolveNonClass($parameter);
            } else {
                $dependencies[] = $this->resolveClass($parameter);
            }
        }

        return $dependencies;
    }

    /**
     * If extra parameters are passed by numeric ID, rekey them by argument name.
     *
     * @param  array  $dependencies
     * @param  array  $parameters
     * @return array
     */
    protected function keyParametersByArgument(array $dependencies, array $parameters)
    {
        foreach ($parameters as $key => $value) {
            if (is_numeric($key)) {
                unset($parameters[$key]);

                $parameters[$dependencies[$key]->name] = $value;
            }
        }

        return $parameters;
    }
}