<?php

namespace Envo\Console;

use Envo\Model\User;
use Envo\Support\File;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

abstract class GeneratorCommand extends Command
{
	/**
	 * The filesystem instance.
	 *
	 * @var File
	 */
	protected $files;
	
	/**
	 * The type of class being generated.
	 *
	 * @var string
	 */
	protected $type;
	
	/**
	 * @var string
	 */
	protected $suffix = '';
	
	/**
	 * Create a new creator command instance.
	 */
	public function __construct()
	{
		parent::__construct();
		
		$this->files = new File();
	}
	
	/**
	 * Get the stub file for the generator.
	 *
	 * @return string
	 */
	abstract protected function getStub();
	
	/**
	 * Execute the console command.
	 *
	 * @return bool|null
	 * @throws \Exception
	 */
	public function handle()
	{
		$module = $this->getModuleClass();
		
		$name = ucfirst($module . '\\'.$this->type.'\\' . $this->getClassName());
		
		if($this->suffix && substr($name, -\strlen($this->suffix)) !== $this->suffix) {
			$name .= $this->suffix;
		}
		
		$path = $this->getPath($name);
		
		// First we will check to see if the class already exists. If it does, we don't want
		// to create the class and overwrite the user's code. So, we will bail out so the
		// code is untouched. Otherwise, we will continue generating this class' files.
		if ($this->alreadyExists($path) && (! $this->input->hasOption('force') || ! $this->option('force'))) {
			$this->line('');
			$this->error($this->getClassName() .' ' . $this->type.' already exists!');
			
			return false;
		}
		
		// Next, we will generate the path to the location where this class' file should get
		// written. Then, we will build the class and make the proper replacements on the
		// stub files so that it gets the correctly formatted namespace and class name.
		$this->makeDirectory($path);
		
		$this->files->put($path, $this->buildClass($name));
		
		//$this->info($this->type.' created successfully. ('. $name .')');
		$this->line("<info>$this->type created successfully:</info> {$name}");
	}
	
	/**
	 * @return string
	 */
	public function getClassName()
	{
		$name = $this->getNameInput();
		
		return ucfirst($name);
	}
	
	/**
	 * @return string
	 */
	public function getModuleClass()
	{
		$module = $this->getModuleInput();
		
		return ucfirst($module);
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
	 * Determine if the class already exists.
	 *
	 * @param $path
	 *
	 * @return bool
	 */
	protected function alreadyExists($path) : bool
	{
		return $this->files->exists($path);
	}
	
	/**
	 * Get the destination class path.
	 *
	 * @param  string  $name
	 * @return string
	 */
	protected function getPath($name) : string
	{
		return APP_PATH . 'app/' .str_replace('\\', '/', $name).'.php';
	}
	
	/**
	 * Build the directory for the class if necessary.
	 *
	 * @param  string  $path
	 * @return string
	 */
	protected function makeDirectory($path) : string
	{
		if (! $this->files->isDirectory(dirname($path))) {
			$this->files->makeDirectory(dirname($path), 0777, true, true);
		}
		
		return $path;
	}
	
	/**
	 * Build the class with the given name.
	 *
	 * @param  string $name
	 *
	 * @return string
	 * @throws \Exception
	 */
	protected function buildClass($name) : string
	{
		$stub = $this->files->get($this->getStub());
		
		return $this->replaceNamespace($stub, $name);
	}
	
	/**
	 * Replace the namespace for the given stub.
	 *
	 * @param  string  $stub
	 * @param  string  $name
	 * @return string
	 */
	protected function replaceNamespace(&$stub, $name) : string
	{
		$class = str_replace($this->getNamespace($name).'\\', '', $name);
		$stub = str_replace(
			[
				'DummyNamespace',
				'DummyRootNamespace',
				'NamespacedDummyUserModel',
				'DummyClass',
				'DummyModule',
				'Dummy',
				'dummy'
			],
			[
				$this->getNamespace($name),
				$this->rootNamespace(),
				config('app.classmap.user', User::class),
				$class,
				$this->getModuleClass(),
				$this->getClassName(),
				lcfirst($class)
			],
			$stub
		);
		
		return $stub;
	}
	
	/**
	 * Get the full namespace for a given class, without the class name.
	 *
	 * @param  string  $name
	 * @return string
	 */
	protected function getNamespace($name) : string
	{
		return trim(implode('\\', \array_slice(explode('\\', $name), 0, -1)), '\\');
	}
	
	/**
	 * Replace the class name for the given stub.
	 *
	 * @param  string  $stub
	 * @param  string  $name
	 * @return string
	 */
	protected function replaceClass($stub, $name) : string
	{
		$class = str_replace($this->getNamespace($name).'\\', '', $name);
		
		return str_replace('DummyClass', $class, $stub);
	}
	
	/**
	 * Get the desired class name from the input.
	 *
	 * @return string
	 */
	protected function getNameInput() : string
	{
		return trim($this->argument('name'));
	}
	
	/**
	 * Get the desired class name from the input.
	 *
	 * @return string
	 */
	protected function getModuleInput() : string
	{
		return trim($this->argument('module'));
	}
	
	/**
	 * Get the root namespace for the class.
	 *
	 * @return string
	 */
	protected function rootNamespace() : string
	{
		return '';
	}
	
	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments() : array
	{
		return [
			['name', InputArgument::REQUIRED, 'The name of the class'],
		];
	}
	
	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions() : array
	{
		return [
			['force', null, InputOption::VALUE_NONE, 'Force this action.'],
		];
	}
}