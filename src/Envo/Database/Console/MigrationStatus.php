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
			
			$io->writeln(' # <fg=blue>Migration status</>');
			$io->newLine();
			
			foreach ($sortedMigrations as $migration => $path) {
				$up = in_array($migration, $ran, false);
				$scaffold = strpos($path, ENVO_PATH) !== false;
				
				if ( ! $up && $scaffold ) {
					continue;
				}
				
				$parts = explode('_', $migration);
				$name = str_replace($parts[0].'_', '', $migration);
				$date = date('Y-m-d H:i:s',strtotime($parts[0]));

				$data[] = [
					$up ? '<info>✔</info>' : '<fg=red>✖</>',
					$up ? '<info>'.$name.'</info>' : $name,
					$up ? '<info>'.$date.'</info>' : $date,
					$scaffold ? '<comment>yes</comment>' : 'no'
				];
			}
			
			$io->table(
				['Status', 'Migration name', 'Date', 'Scaffold'],
				$data
			);
			
			$io->writeln('Legend: <info>✔</info> migrated <fg=red>✖</> not migrated yet');
			
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