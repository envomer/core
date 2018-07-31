<?php

namespace Envo\Foundation\Console;

use Envo\Console\GeneratorCommand;

class MakeDTOCommand extends GeneratorCommand
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $signature = 'make:dto {name : The name of the DTO.} {module : The name of the module.}';
	
	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Create a new DTO class';
	
	/**
	 * The type of class being generated.
	 *
	 * @var string
	 */
	protected $type = 'DTO';
	
	/**
	 * @var string
	 */
	protected $suffix = 'DTO';
	
	/**
	 * Get the stub file for the generator.
	 *
	 * @return string
	 */
	protected function getStub()
	{
		return __DIR__.'/stubs/dto.stub';
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