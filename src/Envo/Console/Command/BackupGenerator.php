<?php

namespace Envo\Command;

class BackupGenerator
{
    public function run()
	{
		echo "Generating backup...\n";
        
		$backup = \System::dbBackup();

        if( ! isset($backup['filename']) ) {
            echo "Failed to generate backup.\n";
            return false;
        }

        unset($backup['msg']);
		$event = new \Core\Events\DatabaseBackupGenerated($backup);

        $success = \Core\Service\DatabaseService::upload($backup['filename']);

        if( $success ) {
            echo "Uploaded backup '{$backup['filename']}'\n";
            new \Core\Events\DatabaseBackupUploaded($success, true, $event);
        }
        else {
            echo "Failed to upload backup '{$backup['filename']}'\n";
            new \Core\Events\DatabaseBackupUploadFailed($backup, true, $event);
        }

		return true;
	}
}