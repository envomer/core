<?php

namespace Envo\Foundation;

class Loader
{
	/**
	 * @var \Phalcon\Loader
	 */
	public $loader;
	
	/**
	 * Loader constructor.
	 *
	 * @param $loader
	 */
	public function __construct($loader)
	{
		$this->loader = $loader;
	}
	
	/**
	 * @param      $name
	 * @param bool $register
	 *
	 * @return mixed
	 */
	public function load($name, $register = false)
	{
		$repositories = array(
			'Cron' => array(
				'Cron' => APP_PATH . '/vendor/mtdowling/cron-expression/src/Cron/'
			)
		);
		
		$this->loader->registerNamespaces($repositories[$name]);
		if( $register ) {
			$this->register();
		}
		return $this;
	}
	
	/**
	 * @param string|array $directory
	 * @param bool $merge
	 */
	public function loadDir($directory, $merge = true)
	{
		if(!is_array($directory)) {
			$directory = [$directory];
		}

		$this->loader->registerDirs($directory, $merge);
	}
	
	/**
	 * @param array $namespaces
	 * @param bool $merge
	 */
	public function loadNamespace(array $namespaces, $merge = true)
	{
		$this->loader->registerNamespaces($namespaces, $merge);
	}
}