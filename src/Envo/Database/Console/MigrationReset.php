<?php

namespace Envo\Database\Console;

class MigrationReset extends BaseCommand
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'migrate:reset {--database= : The database connection to use.}
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
     * Rollback the migration.
     *
     * @return void
     */
    public function handle()
    {
    	$this->manager->reset();
	
		// Once the manager has run we will grab the note output and send it out to
		// the console screen, since the manager itself functions without having
		// any instances of the OutputInterface contract passed into the class.
		foreach ($this->manager->getNotes() as $note) {
			$this->output->writeln($note);
		}
    }
}