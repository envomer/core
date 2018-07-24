<?php

namespace Envo\Foundation\Console;

use Envo\Console\GeneratorCommand;
use Envo\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class MakeAPICommand extends GeneratorCommand
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $signature = 'make:api {name : The name of the api.} {module : The name of the module.}';
	
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
	 * @throws \Exception
	 */
	public function handle()
	{
		if (parent::handle() === false && ! $this->option('force')) {
			return;
		}
		
		if ($this->option('all', false)) {
			$this->input->setOption('model', true);
			$this->input->setOption('migration', true);
			$this->input->setOption('events', true);
		}
		
		if ($this->option('model', false)) {
			//$this->createModel();
		}
		
		if ($this->option('migration', false)) {
			$this->createMigration();
		}
		
		if($this->option('events', false)) {
			$this->createEvents();
		}
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
	
	/**
	 * @return void
	 * @throws \Exception
	 */
	public function createEvents()
	{
		$name = $this->getNameInput();
		$module = $this->getModuleInput();
		$forced = $this->option('force', false);
		
		$this->call('make:event', ['name' => $name . 'Created', 'module' => $module, '--force' => $forced ]);
		$this->call('make:event', ['name' => $name . 'Updated', 'module' => $module, '--force' => $forced ]);
		$this->call('make:event', ['name' => $name . 'Deleted', 'module' => $module, '--force' => $forced ]);
	}
	
	/**
	 * @return void
	 * @throws \Exception
	 */
	public function createModel()
	{
		$name = $this->getNameInput();
		$module = $this->getModuleInput();
		$forced = $this->option('force', false);
		
		$this->call('make:model', ['name' => $name, 'module' => $module, '--force' => $forced]);
	}
	
	/**
	 * @return void
	 * @throws \Exception
	 */
	public function createMigration()
	{
		$name = $this->getNameInput();
		
		$this->call('make:migration', ['name' => 'create_' . Str::snake($name) . '_table', '--create' => '']);
	}
	
	/**
	 * @return array
	 */
	public function getOptions() : array
	{
		$options = parent::getOptions();
		
		return array_merge($options, [
			['all', null, InputOption::VALUE_NONE, 'Create api class including model, events and migration.'],
			['events', null, InputOption::VALUE_NONE, 'Create events for api (created, updated, deleted)'],
			['model', null, InputOption::VALUE_NONE, 'Create model'],
			['migration', null, InputOption::VALUE_NONE, 'Create migration create_NAME_table'],
		]);
	}
}