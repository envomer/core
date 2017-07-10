<?php

namespace Envo\Foundation;

class Translator
{
	/** TODO: add path to config **/
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
				if( strpos($translation, ':' .$k) === false ) {
					if( ! isset($matches) ) {
						preg_match_all('/(?<!\w):\w+/',$translation,$matches);
						$matches = $matches[0];
					}
					$pos = strpos($translation, $matches[$i]);
					if ($pos !== false) {
					    $translation = substr_replace($translation, $param, $pos, strlen($matches[$i]));
					}
				}
				else $translation = str_replace(':' . $k, $param, $translation);
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
	 * @return void
	 */
	public static function get($name, $all = false, $locale = null)
	{
		$locale = $locale ?: self::$locale;
		$search = explode('.', $name);
		if( ! isset(self::$langs[$search[0]]) ) {
			if( isset(self::$alias[$search[0]]) ) {
				$search[0] = self::$alias[$search[0]];
			}

			$path = APP_PATH . self::LANG_PATH . $locale .'/' . $search[0] . '.php';
			if( File::exists($path) ) {
				self::$langs[$search[0]] = require $path;
			} else {
				self::$langs[$search[0]] = [];
			}
		}
		if( $all && count($search) == 1 ) {
			return self::$langs[$search[0]];
		}
		if( ! isset(self::$langs[$search[0]][$search[1]]) ) {
			return $name;
		}

		return self::$langs[$search[0]][$search[1]];
	}
}