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
		if( $compress ) {
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
			'filesize' => filesize($path)
		];
	}

	/**
	 * Get the mysql location
	 * http://stackoverflow.com/questions/20671735/mysqldump-common-install-locations-for-mac-linux
	 */
	public static function mysqldump_location()
	{
		if( is_executable('mysqldump') ) {
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

		foreach($available as $apath) {
			if (is_executable($apath)) {
                return $apath;
            }
		}

		// 4th: auto detection has failed!
		// lets, throw an exception, and ask the user to provide the path instead, manually.
		$message = "Path to \"mysqldump\" binary could not be detected!\n";
		$message .= "Please, specify it inside the configuration file provided!";
		
		throw new RuntimeException($message);
	}

	/**
	 * Find a plugin
	 */
	public static function find($name, $throw = true)
	{
		if( is_executable($name) ) {
            return $name;
        }

		// 1st: use mysqldump location from `which` command.
		$plugin = `which ` . $name;
		if (is_executable($plugin)) {
            return $plugin;
        }

		if( ! $throw ) {
            return null;
        }

		internal_exception('app.pluginNotFound', 404);
	}

	/**
	 * Get commit logs
	 */
	public static function log($branch = 'HEAD')
	{
		$output = self::runCommand(['git', 'log', $branch]);
		$currentVersion = self::runCommand(['git', 'rev-list', $branch]);
		$version = count(explode("\n", $currentVersion)) - 2;

        $lines = explode("\n", $output);
        $commit = new \Illuminate\Support\Collection();
        $changelog = new \Illuminate\Support\Collection();

        foreach ($lines as $key => $line) {
            if (strpos($line, 'commit') === 0 || $key + 1 == count($lines)) {
                if (!$commit->isEmpty()) {
                    // $commit->put('markdown', $commit->get('message'));
                    $commit->put('subject', trim(explode("\n", $commit->get('message'))[0]));
                    $commit->put('version', 'v1.' . $version . '');
                    $changelog->push($commit->toArray());
                    $version--;
                    $commit = collect();
                }
                $commit->put('hash', substr($line, strlen('commit') + 1));
            }
			else if (strpos($line, 'Author') === 0) {
                preg_match_all("/(?:[a-z0-9!#$%&'*+=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+=?^_`{|}~-]+)*|\"(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21\x23-\x5b\x5d-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])*\")@(?:(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?|\[(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?|[a-z0-9-]*[a-z0-9]:(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21-\x5a\x53-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])+)\])/", $line, $emails);
                if($emails) $commit->put('email', $emails[0][0]);
                $commit->put('author', trim(str_replace([$commit->get('email'), '<', '>'], '', substr($line, strlen('Author:') + 1))));
            }
			else if (strpos($line, 'Date') === 0) {
                $commit->put('date', \Carbon\Carbon::createFromFormat('D M d H:i:s Y O', substr($line, strlen('Date:') + 3))->format('Y-m-d H:i:s'));
            }
			elseif (strpos($line, 'Merge') === 0) {
                $commit->put('merge', explode(' ', substr($line, strlen('Merge:') + 1)));
            }
			elseif (!empty($line)) {
                if ($commit->has('message')) {
                    $commit->put('message', $commit->get('message') . "\n" . trim($line));
                } else {
                    $commit->put('message', trim($line));
                }
            }
        }

        return $changelog->toArray();
	}

	/**
	 * Run command
	 */
	public static function runCommand(array $arguments)
	{
		$process = new \Symfony\Component\Process\ProcessBuilder($arguments);
		$process = $process->getProcess()->setWorkingDirectory(APP_PATH);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new \Exception($process);
        }

        return $process->getOutput();
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
			return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
		}
		else {
			return round($size);
		}
	}
}