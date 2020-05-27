<?php

namespace Envo\Database\Migration;

use Envo\AbstractMigration;
use Envo\Support\Arr;
use Envo\Support\Date;
use Envo\Support\File;
use Envo\Support\Str;
use Phalcon\Di;

class Manager
{
	/**
	 * The next batch number
	 *
	 * @var int
	 */
	protected $nextBatchNumber;
	
	/**
	 * The name of the default connection.
	 *
	 * @var \Phalcon\Db\Adapter
	 */
	protected $connection;
	
	/**
	 * The notes for the current operation.
	 *
	 * @var array
	 */
	protected $notes = [];
	
	/**
	 * The paths to all of the migration files.
	 *
	 * @var array
	 */
	protected $paths = [];
	
	/**
	 * @var bool
	 */
	protected $migrationTableExists = false;
	
	protected $migrationFiles;
	
	public $command;
	
	protected $tableName;
	
	/**
	 * Manager constructor.
	 */
	public function __construct()
	{
		$this->connection = Di::getDefault()->get('db');
		
		$this->migrationTableExists = $this->connection->tableExists(
			$this->tableName = config('database.migrations', 'core_migrations')
		);
	}
	
	/**
	 * Run the pending migrations at a given path.
	 *
	 * @param  array|string  $path
	 * @param  array  $options
	 * @return array
	 */
	public function run($path = null, array $options = [])
	{
		$this->notes = [];
		
		// Once we grab all of the migration files for the path, we will compare them
		// against the migrations that have already been run for this package then
		// run each of the outstanding migrations against a database connection.
		$files = $this->getMigrationFiles($path);
		$this->requireFiles($migrations = $this->pendingMigrations(
			$files, $this->getRan()
		));
		
		// Once we have all these migrations that are outstanding we are ready to run
		// we will go ahead and run them "up". This will execute each migration as
		// an operation against a database. Then we'll return this list of them.
		$this->runPending($migrations, $options);
		
		return $migrations;
	}
	
	/**
	 * @return array
	 */
	public function getRan()
	{
		if(!$this->migrationTableExists) {
			return [];
		}
		
		/** @var Model[] $migrations */
		$migrations = Model::repo()->getAll();
		
		$fileNames = [];
		foreach ($migrations as $migration){
			$fileNames[] = $migration->migration;
		}
		
		$this->nextBatchNumber = 1;
		
		if(count($migrations)) {
			$this->nextBatchNumber += $migrations[count($migrations) - 1]->batch;
		}
		
		return $fileNames;
	}
	
	/**
	 * Get the migration files that have not yet run.
	 *
	 * @param  array  $files
	 * @param  array  $ran
	 * @return array
	 */
	protected function pendingMigrations($files, $ran)
	{
		$pending = [];
		
		foreach ($files as $file) {
			$name = $this->getMigrationName($file);
			if(!in_array($name, $ran, true)) {
				$pending[] = $file;
			}
		}
		
		return $pending;
	}
	
	/**
	 * Run an array of migrations.
	 *
	 * @param  array  $migrations
	 * @param  array  $options
	 * @return void
	 */
	public function runPending(array $migrations, array $options = [])
	{
		// First we will just make sure that there are any migrations to run. If there
		// aren't, we will just make a note of it to the developer so they're aware
		// that all of the migrations have been run against this database system.
		if (count($migrations) === 0) {
			$this->note('<info>Nothing to migrate.</info>');
			
			return;
		}
		
		// Next, we will get the next batch number for the migrations so we can insert
		// correct batch number in the database migrations repository when we store
		// each migration's execution. We will also extract a few of the options.
		$batch = $this->nextBatchNumber;
		
		$pretend = $options['pretend'] ?? false;
		
		$step = $options['step'] ?? false;
		
		// Once we have the array of migrations, we will spin through them and run the
		// migrations "up" so the changes are made to the databases. We'll then log
		// that the migration was run so we don't repeat it next time we execute.
		foreach ($migrations as $file) {
			$this->runUp($file, $batch, $pretend);
			
			if ($step) {
				$batch++;
			}
		}
	}
	
	/**
	 * Run "up" a migration instance.
	 *
	 * @param  string  $file
	 * @param  int     $batch
	 * @param  bool    $pretend
	 * @return void
	 */
	protected function runUp($file, $batch, $pretend)
	{
		// First we will resolve a "real" instance of the migration class from this
		// migration file name. Once we have the instances we can run the actual
		// command such as "up" or "down", or we can just simulate the action.
		$migration = $this->resolve(
			$name = $this->getMigrationName($file)
		);
		
		if(!$migration) {
			return;
		}
		
		if ($pretend) {
			return $this->pretendToRun($migration, 'up');
		}
		
		$this->note("<comment>Migrating:</comment> {$name}");
		if(!$this->migrationTableExists) {
			$this->createMigrationTable();
		}
		
		if(!$batch) {
			$batch = $this->nextBatchNumber;
		}
		
		$this->runMigration($migration, 'up');
		
		// Once we have run a migrations class, we will log that it was run in this
		// repository so that we don't try to run it next time we do a migration
		// in the application. A migration repository keeps the migrate order.
		$this->logMigration($name, $batch);
		
		$this->note("<info>Migrated:</info>  {$name}");
	}
	
	/**
	 * Rollback the last migration operation.
	 *
	 * @param  array|string $paths
	 * @param  array  $options
	 * @return array
	 */
	public function rollback($paths, array $options = [])
	{
		$this->notes = [];
		
		// We want to pull in the last batch of migrations that ran on the previous
		// migration operation. We'll then reverse those migrations and run each
		// of them "down" to reverse the last migration "operation" which ran.
		$migrations = $this->getMigrationsForRollback($options);
		
		if (count($migrations) === 0) {
			$this->note('<info>Nothing to rollback.</info>');
			
			return [];
		}
		
		return $this->rollbackMigrations($migrations, $paths, $options);
	}
	
	/**
	 * Get the migrations for a rollback operation.
	 *
	 * @param  array  $options
	 * @return array
	 */
	protected function getMigrationsForRollback(array $options)
	{
		if (($steps = $options['step'] ?? 0) > 0) {
			return $this->repository->getMigrations($steps);
		}

		return $this->getLast();
	}
	
	/**
	 * Rollback the given migrations.
	 *
	 * @param  array  $migrations
	 * @param  array|string  $paths
	 * @param  array  $options
	 * @return array
	 */
	protected function rollbackMigrations($migrations, $paths, array $options)
	{
		$rolledBack = [];
		
		$this->requireFiles($files = $this->getMigrationFiles($paths));
		
		// Next we will run through all of the migrations and call the "down" method
		// which will reverse each migration in order. This getLast method on the
		// repository already returns these migration's names in reverse order.
		foreach ($migrations as $migration) {
			$migration = (object) $migration;
			
			if (! $file = Arr::get($files, $migration->migration)) {
				$this->note("<fg=red>Migration not found:</> {$migration->migration}");
				
				continue;
			}
			
			$rolledBack[] = $file;
			
			$this->runDown(
				$file, $migration,
				$options['pretend'] ?? false
			);
		}
		
		return $rolledBack;
	}
	
	/**
	 * Rolls all of the currently applied migrations back.
	 *
	 * @param  array|string $paths
	 * @param  bool  $pretend
	 * @return array
	 */
	public function reset(array $paths = [], $pretend = false)
	{
		$this->notes = [];
		
		// Next, we will reverse the migration list so we can run them back in the
		// correct order for resetting this database. This will allow us to get
		// the database back into its "empty" state ready for the migrations.
		$migrations = array_reverse($this->getRan());
		
		if (count($migrations) === 0) {
			$this->note('<info>Nothing to rollback.</info>');
			
			return [];
		}
		
		return $this->resetMigrations($migrations, $paths, $pretend);
	}
	
	/**
	 * Reset the given migrations.
	 *
	 * @param  array  $migrations
	 * @param  array  $paths
	 * @param  bool  $pretend
	 * @return array
	 */
	protected function resetMigrations(array $migrations, array $paths, $pretend = false)
	{
		// Since the getRan method that retrieves the migration name just gives us the
		// migration name, we will format the names into objects with the name as a
		// property on the objects so that we can pass it to the rollback method.
		$migrations = array_map(function($item) {
			return (object) ['migration' => $item];
		}, $migrations);
		
		return $this->rollbackMigrations(
			$migrations, $paths, compact('pretend')
		);
	}
	
	/**
	 * Run "down" a migration instance.
	 *
	 * @param  string  $file
	 * @param  Model  $migration
	 * @param  bool    $pretend
	 * @return void
	 */
	protected function runDown($file, $migration, $pretend)
	{
		// First we will get the file name of the migration so we can resolve out an
		// instance of the migration. Once we get an instance we can either run a
		// pretend execution of the migration or we can run the real migration.
		$instance = $this->resolve(
			$name = $this->getMigrationName($file)
		);
		
		if(!$instance) {
			return;
		}
		
		$this->note("<comment>Rolling back:</comment> {$name}");
		
		if ($pretend) {
			return $this->pretendToRun($instance, 'down');
		}
		$this->runMigration($instance, 'down');
		
		// Once we have successfully run the migration "down" we will remove it from
		// the migration repository so it will be considered to have not been run
		// by the application then will be able to fire by any later operation.
		//$this->repository->delete($migration);
		Model::repo()->execute("DELETE FROM {$this->tableName} WHERE migration = :migration", [
			'migration' => $migration->migration
		]);
		
		$this->note("<info>Rolled back:</info>  {$name}");
	}
	
	/**
	 * Run a migration inside a transaction if the database supports it.
	 *
	 * @param  object  $migration
	 * @param  string  $method
	 * @return void
	 */
	protected function runMigration($migration, $method)
	{
		$callback = function () use ($migration, $method) {
			if (method_exists($migration, $method)) {
				$migration->{$method}();
			}
		};
		
		$callback();
	}
	
	/**
	 * Pretend to run the migrations.
	 *
	 * @param  object  $migration
	 * @param  string  $method
	 *
	 * @return bool
	 */
	protected function pretendToRun($migration, $method)
	{
		foreach ($this->getQueries($migration, $method) as $query) {
			$name = get_class($migration);
			
			$this->note("<info>{$name}:</info> {$query['query']}");
		}
		
		return true;
	}
	
	/**
	 * Get all of the queries that would be run for a migration.
	 *
	 * @param  object  $migration
	 * @param  string  $method
	 * @return array
	 */
	protected function getQueries($migration, $method)
	{
		// Now that we have the connections we can resolve it and pretend to run the
		// queries against the database returning the array of raw SQL statements
		// that would get fired against the database system for this migration.
		$db = $this->resolveConnection(
			$migration->getConnection()
		);
		
		return $db->pretend(function () use ($migration, $method) {
			if (method_exists($migration, $method)) {
				$migration->{$method}();
			}
		});
	}
	
	/**
	 * Resolve a migration instance from a file.
	 *
	 * @param  string  $file
	 * @return mixed|AbstractMigration
	 */
	public function resolve($file)
	{
		$class = Str::studly(implode('_', array_slice(explode('_', $file), 1)));
		
		if(!class_exists($class)) {
			$this->note("<error>Class not found:</error> {$class} <info>in {$file}</info>");
			return null;
		}
		
		return new $class($this->connection);
	}
	
	/**
	 * Get all of the migration files in a given path.
	 *
	 * @param  string|array  $paths
	 * @return array
	 */
	public function getMigrationFiles($paths = null)
	{
		if(is_string($paths)) {
			$paths = [$paths];
		}
		
		if(!$paths) {
			$paths = [APP_PATH . 'resources/database/migrations'];
		}
		
		$commands = ['migrate:rollback', 'migrate:status', 'migrate:reset', 'migrate:refresh'];
		if(in_array($this->command, $commands, false)) {
			$paths[] = ENVO_PATH . '../migrations';
		}
		
		$files = $this->migrationFiles ?: [];
		if(!$files) {
			foreach ($paths as $path) {
				$files[] = File::files($path);
			}
			
			$files = array_merge(...$files);
		}
		
		$sorted = [];
		foreach ($files as $file){
			$sorted[$this->getMigrationName($file)] = $file;
		}
		
		return $sorted;
	}
	
	/**
	 * Require in all the migration files in a given path.
	 *
	 * @param  array   $files
	 * @return void
	 */
	public function requireFiles(array $files)
	{
		foreach ($files as $file) {
			require_once $file;
		}
	}
	
	/**
	 * Get the name of the migration.
	 *
	 * @param  string  $path
	 * @return string
	 */
	public function getMigrationName($path)
	{
		return str_replace('.php', '', basename($path));
	}
	
	/**
	 * Register a custom migration path.
	 *
	 * @param  string  $path
	 * @return void
	 */
	public function path($path)
	{
		$this->paths = array_unique(array_merge($this->paths, [$path]));
	}
	
	/**
	 * Get all of the custom migration paths.
	 *
	 * @return array
	 */
	public function paths()
	{
		return $this->paths;
	}
	
	/**
	 * Raise a note event for the migrator.
	 *
	 * @param  string  $message
	 * @return void
	 */
	protected function note($message)
	{
		$this->notes[] = $message;
	}
	
	/**
	 * Get the notes for the last operation.
	 *
	 * @return array
	 */
	public function getNotes()
	{
		return $this->notes;
	}
	
	/**
	 * Create migrations table
	 */
	public function createMigrationTable()
	{
		$table = new Table($this->tableName);
		$table->string('migration');
		$table->integer('batch');
		$table->dateTime('migrated_at');
		
		$this->note("<comment>Creating migrations table:</comment> {$this->tableName}");
		
		$this->connection->createTable($table->name, null, [
			'columns' => $table->columns
		]);
		
		$this->migrationTableExists = true;
		$this->nextBatchNumber = 1;
	}
	
	public function getLastBatchNumber()
	{
		$query = $this->connection->query('select max(batch) as batch from ' . $this->tableName);
		
		return $query->fetch()['batch'];
	}
	
	/**
	 * @return array|\Phalcon\Mvc\Model\Resultset\Simple
	 */
	public function getLast()
	{
		if(! ($lastBatch = $this->getLastBatchNumber())) {
			return [];
		}
		
		return Model::repo()->where('batch', $lastBatch)->get();
	}
	
	public function logMigration($name, $batch)
	{
		$migration = new Model();
		$migration->migration = $name;
		$migration->batch = $batch;
		$migration->migrated_at = Date::now();
		
		return $migration->save();
	}
	
	public function setMigrationFiles($files)
	{
		$this->migrationFiles = $files;
	}
}