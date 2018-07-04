<?php

namespace Envo\Console;

use Envo\Support\File;
use Envo\Support\Str;
use Symfony\Component\Console\Input\InputArgument;

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
	 * Create a new controller creator command instance.
	 *
	 * @param  \Illuminate\Filesystem\Filesystem  $files
	 * @return void
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
	 */
	public function handle()
	{
		$module = $this->getModuleInput();
		$name = $this->getNameInput();
		
		$name = ucfirst($module . '\\'.$this->type.'\\' . ucfirst($name)) . $this->suffix;
		
		$path = $this->getPath($name);
		
		// First we will check to see if the class already exists. If it does, we don't want
		// to create the class and overwrite the user's code. So, we will bail out so the
		// code is untouched. Otherwise, we will continue generating this class' files.
		if (! $this->input->hasOption('force') &&
			$this->alreadyExists($path)) {
			$this->error($this->type.' already exists!');
			
			return false;
		}
		
		die(var_dump($path, $this->buildClass($name)));
		
		// Next, we will generate the path to the location where this class' file should get
		// written. Then, we will build the class and make the proper replacements on the
		// stub files so that it gets the correctly formatted namespace and class name.
		$this->makeDirectory($path);
		
		$this->files->put($path, $this->buildClass($name));
		
		$this->info($this->type.' created successfully.');
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
	 * @param  string  $rawName
	 * @return bool
	 */
	protected function alreadyExists($path)
	{
		return $this->files->exists($path);
	}
	
	/**
	 * Get the destination class path.
	 *
	 * @param  string  $name
	 * @return string
	 */
	protected function getPath($name)
	{
		return APP_PATH . 'app/' .str_replace('\\', '/', $name).'.php';
	}
	
	/**
	 * Build the directory for the class if necessary.
	 *
	 * @param  string  $path
	 * @return string
	 */
	protected function makeDirectory($path)
	{
		if (! $this->files->isDirectory(dirname($path))) {
			$this->files->makeDirectory(dirname($path), 0777, true, true);
		}
		
		return $path;
	}
	
	/**
	 * Build the class with the given name.
	 *
	 * @param  string  $name
	 * @return string
	 */
	protected function buildClass($name)
	{
		$stub = $this->files->get($this->getStub());
		
		return $this->replaceNamespace($stub, $name)->replaceClass($stub, $name);
	}
	
	/**
	 * Replace the namespace for the given stub.
	 *
	 * @param  string  $stub
	 * @param  string  $name
	 * @return $this
	 */
	protected function replaceNamespace(&$stub, $name)
	{
		$stub = str_replace(
			['DummyNamespace', 'DummyRootNamespace', 'NamespacedDummyUserModel'],
			[$this->getNamespace($name), $this->rootNamespace(), config('auth.providers.users.model')],
			$stub
		);
		
		return $this;
	}
	
	/**
	 * Get the full namespace for a given class, without the class name.
	 *
	 * @param  string  $name
	 * @return string
	 */
	protected function getNamespace($name)
	{
		return trim(implode('\\', array_slice(explode('\\', $name), 0, -1)), '\\');
	}
	
	/**
	 * Replace the class name for the given stub.
	 *
	 * @param  string  $stub
	 * @param  string  $name
	 * @return string
	 */
	protected function replaceClass($stub, $name)
	{
		$class = str_replace($this->getNamespace($name).'\\', '', $name);
		
		return str_replace('DummyClass', $class, $stub);
	}
	
	/**
	 * Get the desired class name from the input.
	 *
	 * @return string
	 */
	protected function getNameInput()
	{
		return trim($this->argument('name'));
	}
	
	/**
	 * Get the desired class name from the input.
	 *
	 * @return string
	 */
	protected function getModuleInput()
	{
		return trim($this->argument('module'));
	}
	
	/**
	 * Get the root namespace for the class.
	 *
	 * @return string
	 */
	protected function rootNamespace()
	{
		return '';
	}
	
	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return [
			['name', InputArgument::REQUIRED, 'The name of the class'],
		];
	}
}