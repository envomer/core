<?php

namespace Envo\Foundation\Console;

use Envo\Console\Command;
use Envo\Foundation\Config;
use Envo\Foundation\Event\DatabaseBackupGenerated;
use Envo\Support\System;

class BackupGeneratorCommand extends Command
{
    protected $name = 'make:backup';

    public function handle()
	{
        $this->info('Generate backup...');
        
		$backup = System::dbBackup();

        if( ! isset($backup['filename']) ) {
            echo "Failed to generate backup.\n";
            return false;
        }

        unset($backup['msg']);
		$event = new DatabaseBackupGenerated($backup);

        $success = DatabaseService::upload($backup['filename']);

        if( $success ) {
            echo "Uploaded backup '{$backup['filename']}'\n";
            new DatabaseBackupUploaded($success, true, $event);
        }
        else {
            echo "Failed to upload backup '{$backup['filename']}'\n";
            new DatabaseBackupUploadFailed($backup, true, $event);
        }

		return true;
	}
}