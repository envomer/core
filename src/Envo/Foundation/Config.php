<?php

namespace Envo\Foundation;

class Config
{
	protected static $configs = [];
	const CONFIG_PATH = 'config/';

	/**
	 * Get configuation item using dot notation
	 * eg: app.user will extract the information from
	 * APP_PATH/config/app.php -> user
	 *
	 * @param string $name
	 * @param mixed $default
	 * 
	 * @return mixed
	 */
	public static function get($name, $default = null)
	{
		$search = explode('.', $name);
		if( ! isset(self::$configs[$search[0]]) ) {
			self::$configs[$search[0]] = require APP_PATH . self::CONFIG_PATH . $search[0] . '.php';
		}

		if( count($search) == 1 ) {
			return self::$configs[$search[0]];
		}

		if( ! isset(self::$configs[$search[0]][$search[1]]) ) {
			return $default;
		}

		return self::$configs[$search[0]][$search[1]];
	}

	/**
	 * Get the default database connection
	 * 
	 * @return array
	 */
	public static function database()
	{
		$database = self::get('database');
		$connection = $database['connections'][$database['default']];
		return $connection;
	}

}
