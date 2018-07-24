<?php

namespace Envo\Foundation\Console;

use Envo\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class MakeAPICommand extends GeneratorCommand
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $signature = 'make:api {name : The name of the migration.} {module : The name of the module.}';
	
	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Create a new API class';
	
	/**
	 * The type of class being generated.
	 *
	 * @var string
	 */
	protected $type = 'API';
	
	/**
	 * @var string
	 */
	protected $suffix = 'API';
	
	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function handle()
	{
		if (parent::handle() === false && ! $this->option('force')) {
			return;
		}
		
		if ($this->option('all', false)) {
			$this->input->setOption('factory', true);
			$this->input->setOption('migration', true);
			$this->input->setOption('controller', true);
			$this->input->setOption('resource', true);
		}
		
		if ($this->option('factory', false)) {
			//$this->createFactory();
		}
		
		if ($this->option('migration', false)) {
			//$this->createMigration();
		}
		
		if ($this->option('controller', false) || $this->option('resource', false)) {
			//$this->createController();
		}
	}
	
	/**
	 * Create a model factory for the model.
	 *
	 * @return void
	 */
	protected function createFactory()
	{
		$factory = Str::studly(class_basename($this->argument('name')));
		
		$this->call('make:factory', [
			'name' => "{$factory}Factory",
			'--model' => $this->argument('name'),
		]);
	}
	
	/**
	 * Create a migration file for the model.
	 *
	 * @return void
	 */
	protected function createMigration()
	{
		$table = Str::plural(Str::snake(class_basename($this->argument('name'))));
		
		$this->call('make:migration', [
			'name' => "create_{$table}_table",
			'--create' => $table,
		]);
	}
	
	/**
	 * Get the stub file for the generator.
	 *
	 * @return string
	 */
	protected function getStub()
	{
		return __DIR__.'/stubs/api.stub';
	}
	
	/**
	 * Get the default namespace for the class.
	 *
	 * @param  string  $rootNamespace
	 * @return string
	 */
	protected function getDefaultNamespace($rootNamespace)
	{
		return $rootNamespace;
	}
}