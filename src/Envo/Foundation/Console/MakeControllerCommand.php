<?php

namespace Envo\Foundation\Console;

use Envo\Console\GeneratorCommand;

class MakeControllerCommand extends GeneratorCommand
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $signature = 'make:controller {name : The name of the controller.} {module : The name of the module.}';
	
	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Create a new controller class';
	
	/**
	 * The type of class being generated.
	 *
	 * @var string
	 */
	protected $type = 'Controller';
	
	/**
	 * @var string
	 */
	protected $suffix = 'Controller';
	
	/**
	 * Get the stub file for the generator.
	 *
	 * @return string
	 */
	protected function getStub()
	{
		return __DIR__.'/stubs/controller.stub';
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