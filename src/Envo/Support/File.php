<?php

namespace Envo\Support;

use Envo\AbstractException;
use FilesystemIterator;

class File
{
    public static function filesystem($driver)
    {
        switch ($driver) {
            case 'sftp':
                $adapter = new \League\Flysystem\Sftp\SftpAdapter(array(
                    'host' => '',
                    'port' => 21,
                    'username' => '',
                    'password' => '',
                    'privateKey' => env('SFTP_SSH_PATH'),
                    'root' => '/',
                    'timeout' => 10,
                    'directoryPerm' => 0755
                ));
                break;
            
            default:
                $client = new \Dropbox\Client(env('DROPBOX_TOKEN'), env('DROPBOX_SECRET'));
                $adapter = new \League\Flysystem\Dropbox\DropboxAdapter($client);
                break;
        }

        return new \League\Flysystem\Filesystem($adapter, [
            'root' => ''
        ]);
    }

    /**
     * Determine if a file exists.
     *
     * @param  string  $path
     * @return bool
     */
    public static function exists($path)
    {
        return file_exists($path);
    }

    /**
     * Get the contents of a file.
     *
     * @param  string  $path
     * @return string
     *
     * @throws \Exception
     */
    public static function get($path)
    {
        if (self::isFile($path)) {
            return file_get_contents($path);
        }

        throw new \Exception("File does not exist at path {$path}");
    }

    /**
     * Get the returned value of a file.
     *
     * @param  string  $path
     * @return mixed
     *
     * @throws \Exception
     */
    public static function getRequire($path)
    {
        if (self::isFile($path)) {
            return require $path;
        }

        throw new \Exception("File does not exist at path {$path}");
    }

    /**
     * Require the given file once.
     *
     * @param  string  $file
     * @return mixed
     */
    public static function requireOnce($file)
    {
        require_once $file;
    }

    /**
     * Write the contents of a file.
     *
     * @param  string  $path
     * @param  string  $contents
     * @param  bool  $lock
     * @return int
     */
    public static function put($path, $contents, $lock = false)
    {
        return file_put_contents($path, $contents, $lock ? LOCK_EX : 0);
    }

    /**
     * Prepend to a file.
     *
     * @param  string  $path
     * @param  string  $data
     * @return int
     */
    public static function prepend($path, $data)
    {
        if (self::exists($path)) {
            return self::put($path, $data . self::get($path));
        }

        return self::put($path, $data);
    }

    /**
     * Append to a file.
     *
     * @param  string  $path
     * @param  string  $data
     * @return int
     */
    public static function append($path, $data)
    {
        return file_put_contents($path, $data, FILE_APPEND);
    }

    /**
     * Delete the file at a given path.
     *
     * @param  string|array  $paths
     * @return bool
     */
    public static function delete($paths)
    {
        $paths = is_array($paths) ? $paths : func_get_args();

        $success = true;

        foreach ($paths as $path) {
            try {
                if (! @unlink($path)) {
                    $success = false;
                }
            } catch (\Exception $e) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Move a file to a new location.
     *
     * @param  string  $path
     * @param  string  $target
     * @return bool
     */
    public static function move($path, $target)
    {
        return rename($path, $target);
    }

    /**
     * Copy a file to a new location.
     *
     * @param  string  $path
     * @param  string  $target
     * @return bool
     */
    public static function copy($path, $target)
    {
        return copy($path, $target);
    }

    /**
     * Extract the file name from a file path.
     *
     * @param  string  $path
     * @return string
     */
    public static function name($path)
    {
        return pathinfo($path, PATHINFO_FILENAME);
    }

    /**
     * Extract the file extension from a file path.
     *
     * @param  string  $path
     * @return string
     */
    public static function extension($path)
    {
        return pathinfo($path, PATHINFO_EXTENSION);
    }

    /**
     * Get the file type of a given file.
     *
     * @param  string  $path
     * @return string
     */
    public static function type($path)
    {
        return filetype($path);
    }

    /**
     * Get the mime-type of a given file.
     *
     * @param  string  $path
     * @return string|false
     */
    public static function mimeType($path)
    {
        return finfo_file(finfo_open(FILEINFO_MIME_TYPE), $path);
    }

    /**
     * Get the file size of a given file.
     *
     * @param  string  $path
     * @return int
     */
    public static function size($path)
    {
        return filesize($path);
    }

    /**
     * Get the file's last modification time.
     *
     * @param  string  $path
     * @return int
     */
    public static function lastModified($path)
    {
        return filemtime($path);
    }

    /**
     * Determine if the given path is a directory.
     *
     * @param  string  $directory
     * @return bool
     */
    public static function isDirectory($directory)
    {
        return is_dir($directory);
    }

    /**
     * Determine if the given path is writable.
     *
     * @param  string  $path
     * @return bool
     */
    public static function isWritable($path)
    {
        return is_writable($path);
    }

    /**
     * Determine if the given path is a file.
     *
     * @param  string  $file
     * @return bool
     */
    public static function isFile($file)
    {
        return is_file($file);
    }

    /**
     * Find path names matching a given pattern.
     *
     * @param  string  $pattern
     * @param  int     $flags
     * @return array
     */
    public function glob($pattern, $flags = 0)
    {
        return glob($pattern, $flags);
    }
	
	/**
	 * Get an array of all files in a directory.
	 *
	 * @param  string $directory
	 * @param null    $filter
	 *
	 * @return array
	 */
    public static function files($directory, $filter = null)
    {
        $glob = glob($directory.'/*');

        if ($glob === false) {
            return [];
        }
		
        // To get the appropriate files, we'll simply glob the directory and filter
        // out any "files" that are not truly files so we do not end up with any
        // directories in our list, but only true files within the directory.
        return array_filter($glob, function ($file) use ($filter) {
            if( $filter ) {
				return filetype($file) === 'file' && strpos($file, $filter) !== false;
			}

            return filetype($file) === 'file';
        });
    }

    /**
     * Get all of the files from the given directory (recursive).
     *
     * @param  string  $directory
     * @return array
     */
    public static function allFiles($dir, $recursive = true, $basedir = '', $include_dirs = false)
    {
        // return iterator_to_array(Finder::create()->files()->in($directory), false);
        if ($dir == '') {return array();} else {$results = array(); $subresults = array();}
        if (!is_dir($dir)) {$dir = dirname($dir);} // so a files path can be sent
        if ($basedir == '') {$basedir = realpath($dir).DIRECTORY_SEPARATOR;}
    
        $files = scandir($dir);
        foreach ($files as $key => $value){
            if ( ($value != '.') && ($value != '..') ) {
                $path = realpath($dir.DIRECTORY_SEPARATOR.$value);
                if (is_dir($path)) {
                    // optionally include directories in file list
                    if ($include_dirs) {$subresults[] = str_replace($basedir, '', $path);}
                    // optionally get file list for all subdirectories
                    if ($recursive) {
                        $subdirresults = self::allFiles($path, $recursive, $basedir, $include_dirs);
                        $results = array_merge($results, $subdirresults);
                    }
                } else {
                    // strip basedir and add to subarray to separate file list
                    $subresults[] = str_replace($basedir, '', $path);
                }
            }
        }
        // merge the subarray to give the list of files then subdirectory files
        if (count($subresults) > 0) {$results = array_merge($subresults, $results);}
        return $results;
    }

    /**
     * Get all of the directories within a given directory.
     *
     * @param  string  $directory
     * @return array
     */
    public function directories($directory)
    {
        $directories = [];

        foreach (Finder::create()->in($directory)->directories()->depth(0) as $dir) {
            $directories[] = $dir->getPathname();
        }

        return $directories;
    }

    /**
     * Create a directory.
     *
     * @param  string  $path
     * @param  int     $mode
     * @param  bool    $recursive
     * @param  bool    $force
     * @return bool
     */
    public static function makeDirectory($path, $mode = 0755, $recursive = false, $force = false)
    {
        if ($force) {
            return @mkdir($path, $mode, $recursive);
        }

        return mkdir($path, $mode, $recursive);
    }

    /**
     * Copy a directory from one location to another.
     *
     * @param  string  $directory
     * @param  string  $destination
     * @param  int     $options
     * @return bool
     */
    public function copyDirectory($directory, $destination, $options = null)
    {
        if (! $this->isDirectory($directory)) {
            return false;
        }

        $options = $options ?: FilesystemIterator::SKIP_DOTS;

        // If the destination directory does not actually exist, we will go ahead and
        // create it recursively, which just gets the destination prepared to copy
        // the files over. Once we make the directory we'll proceed the copying.
        if (! $this->isDirectory($destination)) {
            $this->makeDirectory($destination, 0777, true);
        }

        $items = new FilesystemIterator($directory, $options);

        foreach ($items as $item) {
            // As we spin through items, we will check to see if the current file is actually
            // a directory or a file. When it is actually a directory we will need to call
            // back into this function recursively to keep copying these nested folders.
            $target = $destination.'/'.$item->getBasename();

            if ($item->isDir()) {
                $path = $item->getPathname();

                if (! $this->copyDirectory($path, $target, $options)) {
                    return false;
                }
            }

            // If the current items is just a regular file, we will just copy this to the new
            // location and keep looping. If for some reason the copy fails we'll bail out
            // and return false, so the developer is aware that the copy process failed.
            else {
                if (! $this->copy($item->getPathname(), $target)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Recursively delete a directory.
     *
     * The directory itself may be optionally preserved.
     *
     * @param  string  $directory
     * @param  bool    $preserve
     * @return bool
     */
    public function deleteDirectory($directory, $preserve = false)
    {
        if (! self::isDirectory($directory)) {
            return false;
        }

        $items = new FilesystemIterator($directory);

        foreach ($items as $item) {
            // If the item is a directory, we can just recurse into the function and
            // delete that sub-directory otherwise we'll just delete the file and
            // keep iterating through each file until the directory is cleaned.
            if ($item->isDir() && ! $item->isLink()) {
                $this->deleteDirectory($item->getPathname());
            }

            // If the item is just a file, we can go ahead and delete it since we're
            // just looping through and waxing all of the files in this directory
            // and calling directories recursively, so we delete the real path.
            else {
                self::delete($item->getPathname());
            }
        }

        if (! $preserve) {
            @rmdir($directory);
        }

        return true;
    }

    /**
     * Empty the specified directory of all files and folders.
     *
     * @param  string  $directory
     * @return bool
     */
    public function cleanDirectory($directory)
    {
        return $this->deleteDirectory($directory, true);
    }
	
	/**
	 * Download file
	 * @param $path
	 */
    public static function download($path)
    {
        $info = pathinfo($path);

        if( ! $info ){
			die(var_dump('not found'));
		}
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Description: File Transfer');
        header('Content-type: ' . $info['extension']);
        header('Content-length: ' . filesize($path));
        header('Content-Disposition: attachment; filename="'.$info['basename'].'"');
        readfile($path);
        die();
    }
	
	/**
	 * Parse csv file
	 *
	 * @param        $path
	 * @param string $separator
	 *
	 * @return array
	 */
    public static function parseCsv($path, $separator = ',')
    {
        $items = array();
        if (($handle = fopen($path, 'r')) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, $separator)) !== FALSE) {
                $items[] = $data;
            }
            fclose($handle);
        }

        return $items;
    }

    public static function maxUploadSize()
    {
        static $max_size = -1;

        if ($max_size < 0) {
            // Start with post_max_size.
            $max_size = System::parseSize(ini_get('post_max_size'));

            // If upload_max_size is less, then reduce. Except if upload_max_size is
            // zero, which indicates no limit.
            $upload_max = System::parseSize(ini_get('upload_max_filesize'));
            if ($upload_max > 0 && $upload_max < $max_size) {
                $max_size = $upload_max;
            }
        }
        return $max_size;
    }
	
	/**
	 * Download file as json
	 *
	 * @param $filename
	 * @param $json
	 */
    public static function downloadJson($filename, $json)
    {
        header('Content-disposition: attachment; filename='.$filename.'.json');
        header('Content-type: application/json');
        if( ! is_string($json) ) {
            $json = json_encode($json);
        }
        
        echo $json;
    }
	
	
	/**
	 * Slightly modified version of http://www.geekality.net/2011/05/28/php-tail-tackling-large-files/
	 * @author  Torleif Berger, Lorenzo Stanco
	 * @link    http://stackoverflow.com/a/15025877/995958
	 * @link    https://gist.github.com/lorenzos/1711e81a9162320fde20
	 * @license http://creativecommons.org/licenses/by/3.0/
	 *
	 * @param      $filePath
	 * @param int  $lines
	 * @param bool $adaptive
	 *
	 * @return string
	 */
	public static function tail($filePath, $lines = 1, $adaptive = true) {
		// Open file
		$f = @fopen($filePath, 'rb');
		if ($f === false){
			return false;
		}
		// Sets buffer size, according to the number of lines to retrieve.
		// This gives a performance boost when reading a few lines from the file.
		if (!$adaptive){
			$buffer = 4096;
		} else {
			$buffer = ($lines < 2 ? 64 : ($lines < 10 ? 512 : 4096));
		}
		
		// Jump to last character
		fseek($f, -1, SEEK_END);
		// Read it and adjust line number if necessary
		// (Otherwise the result would be wrong if file doesn't end with a blank line)
		if (fread($f, 1) !== "\n"){
			--$lines;
		}
		
		// Start reading
		$output = '';
		$chunk = '';
		// While we would like more
		while (ftell($f) > 0 && $lines >= 0) {
			// Figure out how far back we should jump
			$seek = min(ftell($f), $buffer);
			// Do the jump (backwards, relative to where we are)
			fseek($f, -$seek, SEEK_CUR);
			// Read a chunk and prepend it to our output
			$output = ($chunk = fread($f, $seek)) . $output;
			// Jump back to where we started reading
			fseek($f, -mb_strlen($chunk, '8bit'), SEEK_CUR);
			// Decrease our line counter
			$lines -= substr_count($chunk, "\n");
		}
		// While we have too many lines
		// (Because of buffer size we might have read too many)
		while ($lines++ < 0) {
			// Find first newline and remove all text before that
			$output = substr($output, strpos($output, "\n") + 1);
		}
		// Close file and return
		fclose($f);
		return trim($output);
	}
	
	/**
	 * Render a script
	 *
	 * @param $path
	 * @param array $vars
	 *
	 * @return string
	 * @throws AbstractException
	 */
	public static function render($path, array $vars = [])
	{
		if( ! self::exists($path) ) {
			internal_exception('app.renderFilePathNotFound', 500);
		}
		
		if (is_array($vars) && !empty($vars)) {
			$variablesCreated = extract($vars, EXTR_SKIP);
			if ($variablesCreated !== count($vars)) {
				internal_exception('app.extractionFailedScopeModificationAttempted', 500);
			}
		}
		
		ob_start();
		include $path;
		return ob_get_clean();
	}
}