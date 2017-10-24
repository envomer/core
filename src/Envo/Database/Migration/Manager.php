<?php

namespace Envo\Database\Migration;

use Phinx\Util\Util;

class Manager extends \Phinx\Migration\Manager
{
    /**
     * Returns a list of migration files found in the provided migration paths.
     *
     * @return string[]
     */
    protected function getMigrationFiles()
    {
        $files = parent::getMigrationFiles();

        foreach ($files as $key => $file) {
            $files[$key] = realpath($file);
        }

        return $files;
    }
}