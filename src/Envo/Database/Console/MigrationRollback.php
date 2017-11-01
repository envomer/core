<?php

namespace Envo\Database\Console;

use Envo\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class MigrationRollback extends Command
{
	
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'migrate:rollback {--database= : The database connection to use.}
                {--force : Force the operation to run when in production.}
                {--path= : The path of migrations files to be executed.}
                {--pretend : Dump the SQL queries that would be run.}
                {--seed : Indicates if the seed task should be re-run.}
                {--step : Force the migrations to be run so they can be rolled back individually.}';
	
	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Rollback the last database migration';
	
	/**
	 * Execute the console command.
	 *
	 * @return void
	 * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
	 */
	public function handle()
	{
		//if (! $this->confirmToProceed()) {
		//	return;
		//}
		
		//$this->migrator->setConnection($this->option('database'));
		
		$this->manager->rollback(
			$this->option('path'), [
				'pretend' => $this->option('pretend'),
				'step' => (int) $this->option('step'),
			]
		);
		
		// Once the migrator has run we will grab the note output and send it out to
		// the console screen, since the migrator itself functions without having
		// any instances of the OutputInterface contract passed into the class.
		foreach ($this->manager->getNotes() as $note) {
			$this->output->writeln($note);
		}
	}
}