<?php

namespace Envo\Database\Console;

class MigrationStatus extends BaseCommand
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'migrate:status {--database= : The database connection to use.}
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
		$this->printStatus();
	}
	
	/**
	 * Prints the specified environment's migration status.
	 *
	 * @return integer 0 if all migrations are up, or an error code
	 */
	public function printStatus()
	{
		$output = $this->output;
		$migrations = $this->manager->getMigrationFiles();
		if (count($migrations)) {
			$output->writeln('');
			
			$output->writeln(' Status  Date                  <info>Migration Name</info> ');
			$output->writeln('----------------------------------------------------------------------------------');
			
			$sortedMigrations = $this->manager->getMigrationFiles();
			
			foreach ($sortedMigrations as $migration => $path) {
				if (true) {
					$status = '     <info>up</info> ';
				} else {
					$status = '   <error>down</error> ';
				}
				
				$parts = explode('_', $migration);
				$output->writeln(sprintf(
					'%s  %19s  <comment>%s</comment>',
					$status,
					date('Y-m-d H:i:s',strtotime($parts[0])),
					str_replace($parts[0].'_', '', $migration)
				));
				
				$migrations[] = array(
					'migration_status' => trim(strip_tags($status)),
					'migration_id' => sprintf('%14.0f', '192891'),
					'migration_name' => $migration
				);
			}
		} else {
			// there are no migrations
			$output->writeln('');
			$output->writeln('There are no available migrations. Try creating one using the <info>create</info> command.');
		}
		
		// write an empty line
		$output->writeln('');
	}
}