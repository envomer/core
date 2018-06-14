<?php

namespace Envo\Support;

use RuntimeException;

class Str
{
    /**
     * The cache of snake-cased words.
     *
     * @var array
     */
    protected static $snakeCache = [];

    /**
     * The cache of camel-cased words.
     *
     * @var array
     */
    protected static $camelCache = [];

    /**
     * The cache of studly-cased words.
     *
     * @var array
     */
    protected static $studlyCache = [];

    /**
     * Convert a value to camel case.
     *
     * @param  string  $value
     * @return string
     */
    public static function camel($value): string
	{
        if (isset(static::$camelCache[$value])) {
            return static::$camelCache[$value];
        }

        return static::$camelCache[$value] = lcfirst(static::studly($value));
    }

    /**
     * Determine if a given string contains a given substring.
     *
     * @param  string  $haystack
     * @param  string|array  $needles
     * @return bool
     */
    public static function contains($haystack, $needles): bool
	{
        foreach ((array) $needles as $needle) {
            if ($needle !== '' && strpos($haystack, $needle) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if a given string ends with a given substring.
     *
     * @param  string  $haystack
     * @param  string|array  $needles
     * @return bool
     */
    public static function endsWith($haystack, $needles): bool
	{
        foreach ((array) $needles as $needle) {
            if ((string) $needle === substr($haystack, - \strlen($needle))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Cap a string with a single instance of a given value.
     *
     * @param  string  $value
     * @param  string  $cap
     * @return string
     */
    public static function finish($value, $cap): string
	{
        $quoted = preg_quote($cap, '/');

        return preg_replace('/(?:'.$quoted.')+$/', '', $value).$cap;
    }

    /**
     * Determine if a given string matches a given pattern.
     *
     * @param  string  $pattern
     * @param  string  $value
     * @return bool
     */
    public static function is($pattern, $value)
    {
        if ($pattern == $value) {
            return true;
        }

        $pattern = preg_quote($pattern, '#');

        // Asterisks are translated into zero-or-more regular expression wildcards
        // to make it convenient to check if the strings starts with the given
        // pattern such as "library/*", making any string check convenient.
        $pattern = str_replace('\*', '.*', $pattern).'\z';

        return (bool) preg_match('#^'.$pattern.'#', $value);
    }

    /**
     * Return the length of the given string.
     *
     * @param  string  $value
     * @return int
     */
    public static function length($value): int
	{
        return mb_strlen($value);
    }

    /**
     * Limit the number of characters in a string.
     *
     * @param  string  $value
     * @param  int     $limit
     * @param  string  $end
     * @return string
     */
    public static function limit($value, $limit = 100, $end = '...'): string
	{
        if (mb_strwidth($value, 'UTF-8') <= $limit) {
            return $value;
        }

        return rtrim(mb_strimwidth($value, 0, $limit, '', 'UTF-8')) . $end;
    }

    /**
     * Convert the given string to lower-case.
     *
     * @param  string  $value
     * @return string
     */
    public static function lower($value): string
	{
        return mb_strtolower($value, 'UTF-8');
    }

    /**
     * Limit the number of words in a string.
     *
     * @param  string  $value
     * @param  int     $words
     * @param  string  $end
     * @return string
     */
    public static function words($value, $words = 100, $end = '...'): string
	{
        preg_match('/^\s*+(?:\S++\s*+){1,'.$words.'}/u', $value, $matches);

        if (! isset($matches[0]) || \strlen($value) === \strlen($matches[0])) {
            return $value;
        }

        return rtrim($matches[0]).$end;
    }

    /**
	 * Parse a "Class@method" syntax
	 *
     * @param  string  $callback
     * @param  string  $default
     *
     * @return array
     */
    public static function parseCallback($callback, $default): array
	{
        return static::contains($callback, '@') ? explode('@', $callback, 2) : [$callback, $default];
    }
	
	/**
	 * Generate a more truly "random" alpha-numeric string.
	 *
	 * @param  int $length
	 *
	 * @return string
	 *
	 * @throws \Exception
	 */
    public static function random($length = 16): string
	{
        $string = '';

        while (($len = strlen($string)) < $length) {
            $size = $length - $len;

            $bytes = static::randomBytes($size);

            $string .= substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $size);
        }

        return $string;
    }
	
	/**
	 * Generate a more truly "random" bytes.
	 *
	 * @param  int $length
	 *
	 * @return string
	 *
	 * @throws \Exception
	 */
    public static function randomBytes($length = 16): string
	{
        if (PHP_MAJOR_VERSION >= 7 || defined('RANDOM_COMPAT_READ_BUFFER')) {
            $bytes = random_bytes($length);
        } elseif ( \function_exists('openssl_random_pseudo_bytes')) {
            $bytes = \openssl_random_pseudo_bytes($length, $strong);

            if ($bytes === false || $strong === false) {
                throw new RuntimeException('Unable to generate random string.');
            }
        } else {
            throw new RuntimeException('OpenSSL extension or paragonie/random_compat is required for PHP 5 users.');
        }

        return $bytes;
    }

    /**
     * Generate a "random" alpha-numeric string.
     *
     * Should not be considered sufficient for cryptography, etc.
     *
     * @param  int  $length
     * @return string
     */
    public static function quickRandom($length = 16, $alpha = true, $numeric = true)
    {
        if( $alpha && $numeric ) {
            $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        }
        else if($alpha) {
            $pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        }
        else {
            $pool = '0123456789';
        }

        return substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
    }

    /**
     * Compares two strings using a constant-time algorithm.
     *
     * Note: This method will leak length information.
     *
     * Note: Adapted from Symfony\Component\Security\Core\Util\StringUtils.
     *
     * @param  string  $knownString
     * @param  string  $userInput
     * @return bool
     */
    public static function equals($knownString, $userInput): bool
	{
        if (! \is_string($knownString)) {
            $knownString = (string) $knownString;
        }

        if (! \is_string($userInput)) {
            $userInput = (string) $userInput;
        }

        if ( \function_exists('hash_equals')) {
            return hash_equals($knownString, $userInput);
        }

        $knownLength = mb_strlen($knownString, '8bit');

        if (mb_strlen($userInput, '8bit') !== $knownLength) {
            return false;
        }

        $result = 0;

        for ($i = 0; $i < $knownLength; ++$i) {
            $result |= (\ord($knownString[ $i]) ^ \ord($userInput[ $i]));
        }

        return 0 === $result;
    }

    /**
     * Convert the given string to upper-case.
     *
     * @param  string  $value
     * @return string
     */
    public static function upper($value): string
	{
        return mb_strtoupper($value, 'UTF-8');
    }

    /**
     * Convert the given string to title case.
     *
     * @param  string  $value
     * @return string
     */
    public static function title($value): string
	{
        return mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * Generate a URL friendly "slug" from a given string.
     *
     * @param  string  $title
     * @param  string  $separator
     * @return string
     */
    public static function slug($title, $separator = '-') : string
    {
        //$title = static::ascii($title);
		internal_exception('ASCII method missing. @TODO', 500);

        // Convert all dashes/underscores into separator
        $flip = $separator === '-' ? '_' : '-';

        $title = preg_replace('!['.preg_quote($flip).']+!u', $separator, $title);

        // Remove all characters that are not the separator, letters, numbers, or whitespace.
        $title = preg_replace('![^'.preg_quote($separator).'\pL\pN\s]+!u', '', mb_strtolower($title));

        // Replace all separator characters and whitespace by a single separator
        $title = preg_replace('!['.preg_quote($separator).'\s]+!u', $separator, $title);

        return trim($title, $separator);
    }

    /**
     * Convert a string to snake case.
     *
     * @param  string  $value
     * @param  string  $delimiter
     * @return string
     */
    public static function snake($value, $delimiter = '_') : string
    {
        $key = $value.$delimiter;

        if (isset(static::$snakeCache[$key])) {
            return static::$snakeCache[$key];
        }

        if (! ctype_lower($value)) {
            $value = preg_replace('/\s+/', '', $value);

            $value = strtolower(preg_replace('/(.)(?=[A-Z])/', '$1'.$delimiter, $value));
        }

        return static::$snakeCache[$key] = $value;
    }

    /**
     * Determine if a given string starts with a given substring.
     *
     * @param  string  $haystack
     * @param  string|array  $needles
     * @return bool
     */
    public static function startsWith($haystack, $needles) : bool
    {
        foreach ((array) $needles as $needle) {
            if ($needle !== '' && strpos($haystack, $needle) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Convert a value to studly caps case.
     *
     * @param  string  $value
     * @return string
     */
    public static function studly($value): string
	{
        $key = $value;

        if (isset(static::$studlyCache[$key])) {
            return static::$studlyCache[$key];
        }

        $value = ucwords(str_replace(['-', '_'], ' ', $value));

        return static::$studlyCache[$key] = str_replace(' ', '', $value);
    }

    /**
     * Returns the portion of string specified by the start and length parameters.
     *
     * @param  string  $string
     * @param  int  $start
     * @param  int|null  $length
     * @return string
     */
    public static function substr($string, $start, $length = null): string
	{
        return mb_substr($string, $start, $length, 'UTF-8');
    }

    /**
     * Make a string's first character uppercase.
     *
     * @param  string  $string
     * @return string
     */
    public static function ucfirst($string): string
	{
        return static::upper(static::substr($string, 0, 1)).static::substr($string, 1);
    }

    /**
     * Minimize html string
     */
    public static function minimizeHTML($html)
    {
        return preg_replace(array('/ {2,}/', '/<!--.*?-->|\t|(?:\r?\n[ \t]*)+/s'), array(' ', ''), $html );
    }

    /**
     * Generate customer id
     * one character and 6 digits
     */
    public static function generateCustomerId(): string
	{
        $alpha = ucfirst(self::quickRandom(1, true, false));

        while ($alpha === 'O') {
            $alpha = ucfirst(self::quickRandom(1, true, false));
        }

        return $alpha .'-' . self::quickRandom(6, false);
    }
	
	/**
	 * @param     $haystack
	 * @param     $needle
	 * @param int $offset
	 *
	 * @return bool
	 */
    public static function strposa($haystack, $needle, $offset = 0): bool
	{
        if(!\is_array($needle)) {
        	$needle = array($needle);
		}
		
        foreach($needle as $query) {
            if(strpos($haystack, $query, $offset) !== false) {
            	return true; // stop on first true result
			}
        }
		
        return false;
    }
	
	/**
	 * @return string
	 * @throws \Exception
	 */
    public static function guid(): string
	{
        if ( \function_exists('com_create_guid') === true) {
            return trim(com_create_guid(), '{}');
        }

        return sprintf(
        	'%04X%04X-%04X-%04X-%04X-%04X%04X%04X',
			\random_int(0, 65535),
			\random_int(0, 65535),
			\random_int(0, 65535),
			\random_int(16384, 20479),
			\random_int(32768, 49151),
			\random_int(0, 65535),
			\random_int(0, 65535),
			\random_int(0, 65535)
		);
    }
	
	/**
	 * @param $data
	 *
	 * @return string
	 */
    public static function base64url_encode($data): string
	{
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '='); 
    }
	
	/**
	 * @param $data
	 *
	 * @return bool|string
	 */
    public static function base64url_decode($data)
    { 
        return base64_decode(str_pad(strtr($data, '-_', '+/'), \strlen($data) % 4, '=', STR_PAD_RIGHT));
    }
	
	/**
	 * @param string $input
	 *
	 * @return string
	 */
    public static function cleanDb($input) : string
    {
        return htmlentities(trim($input));
    }

    /**
     * Hash
     *
     * @param string $value
     * @param array $options
     * @return boolean
     */
    public static function hash($value, array $options = array()): bool
	{
		$cost = $options['rounds'] ?? 10;
		$hash = password_hash($value, PASSWORD_BCRYPT, ['cost' => $cost]);
	    if ($hash === false) {
	        throw new \RuntimeException('Bcrypt hashing not supported.');
	    }
	    return $hash;
	}
	
	/**
	 * Generate unique id
	 *
	 * @param integer $length
	 *
	 * @return string
	 * @throws \Exception
	 */
    public static function uniqueId($length = 16): string
	{
		$d = date ('d');
		$m = date ('m');
		$y = date ('Y');
		$t = time();
		$dmt = $d+$m+$y+$t;
		$ran = random_int(0,10000000);
		$dmtran = $dmt+$ran;
		$un = uniqid('', true);
		$dmtun = $dmt.$un;
		$mdun = md5($dmtran.$un);
		if( $length ) {
            return substr($mdun, $length);
        }

		return $mdun;
    }
    
    /**
     * Minimum length is 'time' + 1 char
     *
     * @param integer $length
     * @return string
     */
    public static function identifier($length = 32): string
	{
        $time = time();
        return self::quickRandom(($length - 1) - \strlen((string)$time)) . '.' . $time;
    }
}