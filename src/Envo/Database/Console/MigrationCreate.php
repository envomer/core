<?php

namespace Envo\Database\Console;

use Envo\Support\File;
use Envo\Support\Str;

class MigrationCreate extends BaseCommand
{
    /**
     * The console command signature.
     *
     * @var string
     */
	protected $signature = 'migrate:make {name : The name of the migration.}
        {--create= : The table to be created.}
        {--table= : The table to migrate.}
        {--path= : The location where the migration file should be created.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new migration file';
	
	/**
	 * Execute the console command.
	 *
	 * @return void
	 * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
	 */
    public function handle()
    {
		// It's possible for the developer to specify the tables to modify in this
		// schema operation. The developer may also specify if this table needs
		// to be freshly created so we can create the appropriate migrations.
		$name = trim($this->input->getArgument('name'));
		$table = $this->input->getOption('table');
		$create = $this->input->getOption('create') ?: false;
	
		// If no table was given as an option but a create option is given then we
		// will use the "create" option as the table name. This allows the developers
		// to pass a table name into this option as a short-cut for creating.
		if (! $table && is_string($create)) {
			$table = $create;
		
			$create = true;
		}
	
		// Next, we will attempt to guess the table name if this the migration has
		// "create" in the name. This will allow us to provide a convenient way
		// of creating migrations that create new tables for the application.
		if ( !$table && preg_match('/^create_(\w+)_table$/', $name, $matches) ){
			$table = $matches[1];
		
			$create = true;
		}
	
		// Now we are ready to write the migration out to disk. Once we've written
		// the migration out, we will dump-autoload for the entire framework to
		// make sure that the migrations are registered by the class loaders.
		$this->writeMigration($name, $table, $create);
    }
	
	/**
	 * Write the migration file to disk.
	 *
	 * @param  string  $name
	 * @param  string  $table
	 * @param  bool    $create
	 * @return string
	 */
	protected function writeMigration($name, $table, $create)
	{
		$file = pathinfo($this->create(
			$name, $this->getMigrationPath(), $table, $create
		), PATHINFO_FILENAME);
		
		$this->line("<info>Created Migration:</info> {$file}");
	}
	
	/**
	 * @param $name
	 * @param $path
	 * @param $table
	 * @param $create
	 *
	 * @return string
	 */
	protected function create($name, $path, $table, $create)
	{
		if (class_exists($className = $this->getClassName($name))) {
			throw new \InvalidArgumentException("A {$className} class already exists.");
		}
		
		// First we will get the stub file for the migration, which serves as a type
		// of template for the migration. Once we have those we will populate the
		// various place-holders, save the file, and run the post create event.
		$stub = $this->getStub($table, $create);
		File::put(
			$path = $this->getPath($name, $path),
			$this->populateStub($name, $stub, $table)
		);
		
		return $path;
	}
	
	/**
	 * Get the migration stub file.
	 *
	 * @param  string $table
	 * @param  bool   $create
	 *
	 * @return string
	 * @throws \Exception
	 */
	protected function getStub($table, $create)
	{
		if ( null === $table ) {
			return File::get($this->stubPath().'/blank.stub');
		}
		// We also have stubs for creating new tables and modifying existing tables
		// to save the developer some typing when they are creating a new tables
		// or modifying existing tables. We'll grab the appropriate stub here.
		
		$stub = $create ? 'create.stub' : 'update.stub';
		
		return File::get($this->stubPath()."/{$stub}");
	}
	/**
	 * Populate the place-holders in the migration stub.
	 *
	 * @param  string  $name
	 * @param  string  $stub
	 * @param  string  $table
	 * @return string
	 */
	protected function populateStub($name, $stub, $table)
	{
		$stub = str_replace('DummyClass', $this->getClassName($name), $stub);
		// Here we will replace the table place-holders with the table specified by
		// the developer, which is useful for quickly creating a tables creation
		// or update migration from the console instead of typing it manually.
		if ( null !== $table ) {
			$stub = str_replace('DummyTable', $table, $stub);
		}
		return $stub;
	}
	
	/**
	 * Get the path to the stubs.
	 *
	 * @return string
	 */
	public function stubPath()
	{
		return __DIR__.'/stubs';
	}
	
	/**
	 * Get the class name of a migration name.
	 *
	 * @param  string  $name
	 * @return string
	 */
	protected function getClassName($name)
	{
		return Str::studly($name);
	}
	
	/**
	 * Get the full path to the migration.
	 *
	 * @param  string  $name
	 * @param  string  $path
	 * @return string
	 */
	protected function getPath($name, $path)
	{
		return $path.'/'.date('YmdHis').'_'.$name.'.php';
	}
}