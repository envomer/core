<?php

namespace Envo\Foundation;

class Loader
{
	/**
	 * @var \Phalcon\Loader
	 */
	protected $loader;
	
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
	 * @param $directory
	 */
	public function loadDir($directory)
	{
		$directories = $this->loader->getDirs();
		$directories[] = $directory;
		$this->loader->registerDirs($directories);
	}
}