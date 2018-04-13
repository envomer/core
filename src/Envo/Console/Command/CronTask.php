<?php

namespace Envo\Command;

use Cron\CronExpression;
use Symfony\Component\Process\Process;

use Core\Model\CronJob;
use Core\Model\CronJobEntry;

class CronTask extends \Phalcon\Cli\Task
{
	/**
	 * *    *    *    *    *    *
	 * -    -    -    -    -    -
	 * |    |    |    |    |    |
	 * |    |    |    |    |    + year [optional]
	 * |    |    |    |    +----- day of week (0 - 7) (Sunday=0 or 7)
	 * |    |    |    +---------- month (1 - 12)
	 * |    |    +--------------- day of month (1 - 31)
	 * |    +-------------------- hour (0 - 23)
	 * +------------------------- min (0 - 59)
	 */
	public function mainAction()
	{
		echo "Running scheduler...\n";
		// $scheduler = new Illuminate\Console\Scheduling\Schedule;
		$events = array();

		\File::put(APP_PATH . 'storage/framework/logs/cron.txt', \Date::now() . "\n");

		// $events[] = array('expression' => '* * * * * *', 'command' => 'CalendarSendReminderMailsTask');
		$events['CashRegisterSignAutomaticallyTask'] = array('expression' => '* 0-1 1 * * *', 'command' => 'CashRegisterSignAutomaticallyTask');
		$events['BackupGenerator'] = array('expression' => '0 0,9,12,18 * * * *', 'command' => 'BackupGenerator');

		$jobs = CronJob::repo()->getAll();

		foreach($jobs as $job) {
			if( isset($events[$job->name]) ) {
				$events[$job->name]['job'] = $job;
				$events[$job->name]['expression'] = $job->expression;
			} else {
				$events[$job->name] = [
					'expression' => $job->expression,
					'command' => $job->name,
					'job' => $job,
				];
			}
		}

		$events = array_filter($events, function($event) {
            return CronExpression::factory($event['expression'])->isDue();
        });

        $eventsRan = 0;
		$output = '';

        foreach ($events as $event) {
			if( isset($event['job']) ) {
				$job = $event['job'];
			} else {
				$job = $this->getJob($event['command'], $event['expression']);
			}

            $output .= 'Running scheduled command: '. $event['command'] . ".\n";

			$jobEntry = new CronJobEntry();
			$jobEntry->job_id = $job->getId();
			$jobEntry->run = ($eventsRan+1) . '/' . count($events);
			$jobEntry->created_at = \Date::now();
			$jobEntry->save();

			ob_start();
			
            $eventClass = new $event['command'];
            $jobEntry->status = $eventClass->run() ? $jobEntry::STATUS_OK : $jobEntry::STATUS_FAILED;
            
			$jobEntry->output = ob_get_clean();
			$jobEntry->updated_at = \Date::now();
			$jobEntry->save();

			$job->last_run = \Date::now();
			$job->updated_at = \Date::now();
			$job->status = $jobEntry->status;
			if( $job->status == $job::STATUS_FAILED ) {
				$job->failed = $job->failed ? 1 : $job->failed + 1;
			}
			$job->runs++;
			$result = $job->save();
			
			$output .= "Done.\n";
            ++$eventsRan;
        }

        if (count($events) === 0 || $eventsRan === 0) {
            $output .= 'No scheduled commands are ready to run.';
        }

		$output .= "\nComplete.\n";

		echo $output;
		\File::append(APP_PATH . 'storage/framework/logs/cron.txt', $output);

        return true;
	}

	public function forceAction($input)
	{
		$class = reset($input);
		if(! $class || ! class_exists($class) ) {
			echo 'Class does not exist: ' . $class;
			return false;
		}

		$object = new $class;
		$object->run();

		echo "Done.\n";
	}

	public function getJob($name, $expression)
	{
		$job = CronJob::repo()->getByName($name);
		if( ! $job ) {
			$job = new CronJob();
			$job->name = $name;
			$job->expression = $expression;
			$job->created_at = \Date::now();
			$job->save();
		}

		return $job;
	}
}