<?php

namespace Envo\Database\Console;

use Symfony\Component\Console\Style\SymfonyStyle;

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
	 */
	public function printStatus()
	{
		$output = $this->output;
		$migrations = $this->manager->getMigrationFiles();
		$io = new SymfonyStyle($this->input, $this->output);
		$data = [];
		if (count($migrations)) {
			$io->newLine();
			
			$sortedMigrations = $this->manager->getMigrationFiles();
			$ran = $this->manager->getRan();
			asort($sortedMigrations);
			
			foreach ($sortedMigrations as $migration => $path) {
				$status = '<error>down</error> ';
				$scaffold = strpos($path, ENVO_PATH) !== false;
				
				if (in_array($migration, $ran, false)) {
					$status = '<info>up</info> ';
				} else if($scaffold) {
					continue;
				}
				
				$parts = explode('_', $migration);

				$data[] = [
					$status,
					date('Y-m-d H:i:s',strtotime($parts[0])),
					'<comment>'.str_replace($parts[0].'_', '', $migration) .'</comment>'.
					($scaffold ? ' <info>(scaffold)</info>' : '')
				];
			}
			
			$io->table(
				['Status', 'Date', 'Migration name'],
				$data
			);
			
			// write an empty line
			$io->newLine();
		} else {
			// there are no migrations
			$io->newLine();
			$this->comment('There are no available migrations.');
			$output->writeln('Try creating one using the <info>migrate:create</info> command.');
		}
	}
}