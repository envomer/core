<?php

namespace Envo\Foundation\Console;

use Envo\Console\Command;
use Envo\Foundation\Event\DatabaseBackupGenerated;
use Envo\Support\System;

class BackupCommand extends Command
{
	/**
	 * The console command signature.
	 *
	 * @var string
	 */
	protected $signature = 'backup';
	
	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Back up database';

    public function handle()
	{
        $this->info('Generate backup...');
        
		$encrypt = config('database.backup.encrypt') ? ' encrypt' : '""';
		$compress = config('database.backup.compress') ? ' compress ' : ' "" ';
		$nameFormat = config('database.backup.name_format', '%Y%m%d');
		$command = trim('sh ' . ENVO_PATH . "../bin/db.sh" . $compress . $encrypt . ' "' . $nameFormat . '"');
		
		//$this->info($command); // debug
		
		echo shell_exec($command);
  
		// TODO: trigger events
		
		return true;
	}
}