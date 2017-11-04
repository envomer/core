<?php

namespace Envo\Database\Console;

class Migrate extends BaseCommand
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'migrate {--database= : The database connection to use.}
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
	protected $description = 'Run the database migrations';
	
	/**
	 * Execute the console command.
	 *
	 * @return void
	 * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
	 */
	public function handle()
	{
		//$this->prepareDatabase();
		// Next, we will check to see if a path option has been defined. If it has
		// we will use the path relative to the root of this installation folder
		// so that migrations may be run for any path within the applications.
		
		$this->manager->run($this->option('path'), [
			'pretend' => $this->option('pretend'),
			'step' => $this->option('step'),
		]);
		
		// Once the migrator has run we will grab the note output and send it out to
		// the console screen, since the migrator itself functions without having
		// any instances of the OutputInterface contract passed into the class.
		foreach ($this->manager->getNotes() as $note) {
			$this->output->writeln($note);
		}
		
		// Finally, if the "seed" option has been given, we will re-run the database
		// seed task to re-populate the database, which is convenient when adding
		// a migration and a seed at the same time, as it is only this command.
		if ($this->option('seed')) {
			$this->call('db:seed', ['--force' => true]);
		}
	}
}