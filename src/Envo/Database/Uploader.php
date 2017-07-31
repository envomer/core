<?php

namespace Envo\Database;

use League\Flysystem\Adapter\Ftp;
use League\Flysystem\Filesystem;

class Uploader
{
    const FILES_PATH = APP_PATH . 'storage/db';

    public static function upload($filename)
    {
        $response = array(
			'dropbox' => false,
			'world4you cloud' => false,
			'sofortcloud' => false,
			'world4you ftp' => false,
		);

		$settings = array(
		    array(
		    	'name' => 'sofortcloud',
				'baseUri' => 'https://my.sofortcloud.com/remote.php/webdav/',
			    'userName' => env('BACKUP_DAV'),
			    'password' => env('BACKUP_DAV_SECRET'),
			),
			array(
				'name' => 'world4you cloud',
				'baseUri' => 'https://cloudlogin02.world4you.com/remote.php/webdav/',
			    'userName' => env('BACKUP_DAV2'),
			    'password' => env('BACKUP_DAV2_SECRET'),
			)
		);

		$file = \Core\Service\DatabaseRepo::get($filename, false);
		foreach ($settings as $setting) {
			$client = new \Sabre\DAV\Client($setting);
			if( ($head = $client->request('HEAD', $filename)) && $head['statusCode'] == 404 ) {
				$request = $client->request('PUT', $filename, file_get_contents($file));
				$response[$setting['name']] = true;
			}
		}

		$ftpSettings = array(
			// 'name' => 'world4you ftp',
			'host' => env('BACKUP_FTP_HOST'),
		    'username' => env('BACKUP_FTP_USER'),
		    'password' => env('BACKUP_FTP_SECRET'),
		);
		$ftpAdapter = new Ftp($ftpSettings);
		$filesystem = new Filesystem($ftpAdapter);
		$remotePath = basename($file);

		try {
			if( ! $filesystem->has($remotePath) ) {
				$stream = fopen($file, 'r+');
				$response['world4you ftp'] = $filesystem->putStream($remotePath, $stream);

				if (is_resource($stream)) {
				    fclose($stream);
				}
			}
		}
		catch (\Exception $e) {
			$response['world4you ftp'] = $e->getMessage();
		}

		$filesystem = File::filesystem('dropbox');

		try {
			$remotePath = 'Work/asprify/db backup/' . basename($file);
			if( ! $filesystem->has($remotePath) ) {
				$stream = fopen($file, 'r+');
				$response['dropbox'] = $filesystem->putStream($remotePath, $stream);

				if (is_resource($stream)) {
				    fclose($stream);
				}
			}
		} catch (\Exception $e) {
			$response['dropbox'] = $e->getMessage();
		}

        return $response;
    }

	/**
	 * Get all the database files stored on the server
	 */
	public static function getAll($page, $data = null)
	{
		$files = File::files(self::FILES_PATH, 'sql');
		$files = array_reverse($files);

		$all = [];
		foreach($files as $file) {
			$all[] = [
				'path' => $file,
				'size' => filesize($file),
				'file' => basename($file)
			];
		}
		
		return array(
			'data' => $all,
			'page' => 1,
			'limit' => 1000,
			'total_items' => count($files),
			'total_pages' => 1,
		);
	}

	/**
	 * Generate a new database backup
	 */
	public static function save($data = null)
	{
		$file = \System::dbBackup();

		$msg = $file;
		unset($msg['msg']);
		new \Core\Events\DatabaseBackupGenerated($msg);

		return $file;
	}

	/**
	 * Download database backup
	 */
	public static function get($filename, $download = true)
	{
		$file = File::files(self::FILES_PATH, $filename);

		if( ! $file ) {
			throw new \Exception('not found');
		}

		$file = reset($file);
		if( $download ) {
			File::download($file);
		}
		else return $file;
	}
}