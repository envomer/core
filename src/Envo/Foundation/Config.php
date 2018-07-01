<?php

namespace Envo\Foundation;

use Envo\Support\Arr;

class Config
{
	const CONFIG_PATH = 'config/';
	
	protected $configs = [];

	/**
	 * Get configuation item using dot notation
	 * eg: app.user will extract the information from
	 * APP_PATH/config/app.php -> user
	 *
	 * @param string $name
	 * @param mixed $default
	 * 
	 * @return string|array|integer
	 */
	public function get($name, $default = null)
	{
		$search = explode('.', $name);
		if( ! isset($this->configs[$search[0]]) ) {
			if(file_exists(APP_PATH . 'bootstrap/cache/config.php')) {
				$this->configs = require_once APP_PATH . 'bootstrap/cache/config.php';
			} else {
				$file = APP_PATH . self::CONFIG_PATH . $search[0] . '.php';

				if(!file_exists($file)) {
					return $default;
				}

				$this->configs[$search[0]] = require_once $file;
			}

		}

		if( count($search) == 1 ) {
			return $this->configs[$search[0]];
		}

		if( ! isset($this->configs[$search[0]][$search[1]]) ) {
			return $default;
		}

		$key = substr($name, strlen($search[0]) + 1);

		return Arr::get($this->configs[$search[0]], $key, $default);
	}

	/**
	 * Get the default database connection
	 * 
	 * @return array
	 */
	public function database()
	{
		$database = $this->get('database');
		$connection = $database['connections'][$database['default']];
		return $connection;
	}

	public function all()
	{
		$files = glob(APP_PATH . self::CONFIG_PATH . '*.php');

		$output = [];
		foreach ($files as $file) {
			$name = basename($file, '.php');
			$output[$name] = include $file;
		}

		return $output;
	}

}
