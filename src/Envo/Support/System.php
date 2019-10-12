<?php

namespace Envo\Support;

use Envo\Foundation\Config;

/**
 * System class
 * Responsible for handling server related functions
 * such as database backup and other server funcs
 */
class System
{
	/**
	 * Make a backup of the database using the configuration
	 * located in the database.php or .env file.
	 * Store the generated file into the /storage/db/ folder
	 *
	 * @param bool $compress
	 *
	 * @return array
	 */
	public static function dbBackup($compress = true)
	{
		//ENTER THE RELEVANT INFO BELOW
		$config = Config::database();
		$url = env('APP_URL', 'localhost');
		$url = parse_url($url);
		$host = str_replace('.', '2', $url['host']);
		$path = APP_PATH . 'storage/framework/backup/' . ($filename = ($host) .'-mdb-' . date('YmdHis') .'.sql');

		//DO NOT EDIT BELOW THIS LINE
		//Export the database and output the status to the page
		$command = self::mysqldump_location() . ' --opt -h' .$config['host'] .' -u' .$config['username'] .' ' .($config['password'] ? '-p'. $config['password'] : '') .' ' .$config['database'];
		if ( $compress ) {
			$path .= '.bz2';
			$filename .= '.bz2';
			$command .= ' | bzip2 > ' . $path;
		} else {
			$command .= ' > ' . $path;
		}
		$worked = null;
		$output = [];
		$response = '';

		exec($command,$output,$worked);

		switch($worked){
			case 0:
				$response = 'Database ' .$config['database'] .' successfully exported as ' . $filename . ($compress ? ' (with compression)' : '');
				break;
			case 1:
				$response = 'There was a warning during the export of <b>' .$config['database'] .'</b> to <b>~/' .$path .'</b>';
				break;
			case 2:
				$response = 'There was an error during export. Please check your values:<br/><br/><table><tr><td>MySQL Database Name:</td><td><b>' .$config['database'] .'</b></td></tr><tr><td>MySQL User Name:</td><td><b>' .$mysqlUserName .'</b></td></tr><tr><td>MySQL Password:</td><td><b>NOTSHOWN</b></td></tr><tr><td>MySQL Host Name:</td><td><b>' .$config['host'] .'</b></td></tr></table>';
			break;
		}
		
		return [
			'msg' => $response,
			'filename' => $filename,
			'size' => filesize($path)
		];
	}

	/**
	 * Get the mysql location
	 * http://stackoverflow.com/questions/20671735/mysqldump-common-install-locations-for-mac-linux
	 */
	public static function mysqldump_location()
	{
		if ( is_executable('mysqldump') ) {
            return 'mysqldump';
        }

		// 1st: use mysqldump location from `which` command.
		$mysqldump = `which mysqldump`;
		if (is_executable($mysqldump)) {
            return $mysqldump;
        }

		// 2nd: try to detect the path using `which` for `mysql` command.
		$mysqldump = dirname(`which mysql`) . "/mysqldump";
		if (is_executable($mysqldump)) {
            return $mysqldump;
        }

		// 3rd: detect the path from the available paths.
		// you can add additional paths you come across, in future, here.
		$available = array(
			'/usr/bin/mysqldump', // Linux
			'/usr/local/mysql/bin/mysqldump', //Mac OS X
			'/usr/local/bin/mysqldump', //Linux
			'/usr/mysql/bin/mysqldump' //Linux
		);

		foreach($available as $path) {
			if (is_executable($path)) {
                return $path;
            }
		}

		// 4th: auto detection has failed!
		// lets, throw an exception, and ask the user to provide the path instead, manually.
		$message = 'Path to "mysqldump" binary could not be detected!';
		$message .= 'Please, specify it inside the configuration file provided!';
		
		throw new RuntimeException($message);
	}

	/*
	 * Parse system sizes such as 2M
	 */
	public static function parseSize($size)
	{
		$unit = preg_replace('/[^bkmgtpezy]/i', '', $size); // Remove the non-unit characters from the size.
		$size = preg_replace('/[^0-9\.]/', '', $size); // Remove the non-numeric characters from the size.
		if ($unit) {
			// Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
			return round($size * (1024 ** stripos('bkmgtpezy', $unit[0])));
		}
		
		return round($size);
	}
}