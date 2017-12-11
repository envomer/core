<?php

namespace Envo\Support;

class Translator
{
	/**
	 * TODO: add path to config
	 **/
	const LANG_PATH = 'resources/lang/';
	const LANG_DE = 0;
	const LANG_EN = 1;

	protected static $langs = [];
	protected static $locale = 'de';

	protected static $languages = [
		self::LANG_DE => 'de',
		self::LANG_EN => 'en'
	];

	protected static $alias = [];
	
	/**
	 * Translator constructor.
	 */
	public function __construct()
	{
		self::$locale = config('app.locale');
	}
	
	/**
	 * Set locale
	 *
	 * @param string $locale
	 * @return void
	 */
	public static function setLocale($locale)
	{
		self::$locale = self::$languages[$locale];
	}

	/**
	 * Get translation of given word
	 *
	 * @param string $name
	 * @param string|array $params
	 * @param string $locale
	 * @return string|array
	 */
	public static function lang($name, $params = null, $locale = null)
	{
		$translation = self::get($name, $params, $locale);
		if( $params && is_array($params) ) {
			$i = 0;
			foreach ($params as $k => $param) {
				if(is_array($translation) ) {
					return $translation;
				}
				
				if( strpos($translation, ':' .$k) === false ) {
					preg_match_all('/(?<!\w):\w+/', $translation, $matches);
					$matches = $matches[0];

					if( ! $matches ) {
						return $translation;
					}
					
					$pos = strpos($translation, $matches[$i]);
					if ($pos !== false) {
					    $translation = substr_replace($translation, $param, $pos, strlen($matches[$i]));
					}
				}
				else {
					$translation = str_replace(':' . $k, $param, $translation);
				}
				$i++;
			}
		}

		return $translation;
	}

	/**
	 * Choice
	 *
	 * @param string $name
	 * @param int $count
	 * @return string|array
	 */
	public static function choice($name, $count = 1, $locale = null)
	{
		$name = self::get($name, $count, $locale);
		$names = explode('|', $name);
		if( $count > 1 && isset($names[1]) ) {
			return $names[1];
		}
		
		return $names[0];
	}

	/**
	 * Get translated word from file
	 *
	 * @param string $name
	 * @param boolean $all
	 * @param string $locale
	 * @return mixed
	 */
	public static function get($name, $all = false, $locale = null)
	{
		$locale = $locale ?: self::$locale;
		$search = explode('.', $name);

		if( strpos($search[0], ' ') !== false ) {
			return $name;
		}
		
		if( ! isset(self::$langs[$search[0]]) ) {
			if( isset(self::$alias[$search[0]]) ) {
				$search[0] = self::$alias[$search[0]];
			}

			$path = APP_PATH . self::LANG_PATH . $locale .'/' . $search[0] . '.php';
			if( file_exists($path) ) {
				self::$langs[$search[0]] = require $path;
			} else {
				self::$langs[$search[0]] = [];
			}
		}
		if( $all && count($search) === 1 ) {
			return self::$langs[$search[0]];
		}
		if( ! isset(self::$langs[$search[0]][$search[1]]) ) {
			return $name;
		}

		return self::$langs[$search[0]][$search[1]];
	}
}