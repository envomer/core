<?php

namespace Envo\Queue;

class Manager
{
    /**
     * The connector for the queue.
     *
     * @var string
     */
    protected $connector;

    /**
     * Set up connector
     *
     * @param [type] $connectorName
     */
    public function __construct($connectorName = null)
    {
        if( ! $connectorName ) {
            $connectorName = config('app.queue.connector', 'mysql');
        }

        $connectorClass = '\Envo\Queue\Connector\\' . ucfirst($connectorName) . 'Connector';
        if( ! class_exists($connectorClass) ) {
            internal_exception('app.queueConnectorNotFound', 500);
        }

        $this->connector = new $connectorClass;
    }

    /**
     * Push a new job onto the queue.
     *
     * @param  string  $queue
     * @param  string  $job
     * @param  mixed   $data
     * @return mixed
     */
    public function push($job, $delay = null, $queue = null)
    {
        $data = $this->sleep($job);

        return $this->connector->store($data, $delay, $queue);
    }

    /**
     * Push a new job onto the queue after a delay.
     *
     * @param  string  $queue
     * @param  \DateTime|int  $delay
     * @param  string  $job
     * @param  mixed   $data
     * @return mixed
     */
    public function laterOn($queue, $delay, $job, $data = '')
    {
        return $this->later($delay, $job, $data, $queue);
    }

    /**
     * Push an array of jobs onto the queue.
     *
     * @param  array   $jobs
     * @param  mixed   $data
     * @param  string  $queue
     * @return mixed
     */
    public function bulk($jobs, $data = '', $queue = null)
    {
        foreach ((array) $jobs as $job) {
            $this->push($job, $data, $queue);
        }
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

    public function getNextJobs($limit = 5)
    {
        return $this->connector->getNextJobs($limit) ?: null;
    }

    public function work(Job $job)
    {
        $class = $this->wakeup($job->payload);

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

            $this->connector->retryJob($job);
        }

        $job->done = 1;
        if ( $job->status !== $job::STATUS_FAILED ) {
            $this->connector->deleteJob($job);
        }

        return $result;
    }

    /**
     * Restore the model after serialization.
     *
     * @return void
     */
    protected function wakeup($data)
    {
        $class = $data['class'];
        $parameters = isset($data['properties']) ? $data['properties'] : [];

        $reflector = new \ReflectionClass($class);

        $constructor = $reflector->getConstructor();
        $dependencies = $constructor ? $constructor->getParameters() : [];

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