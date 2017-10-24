<?php

use Envo\AbstractMigration;

class CreatePermissions extends AbstractMigration
{
    public function up()
    {
        $this->create('core_permissions', function($table) {
            $table->increments('id');
            
            $table->string('key');
            $table->string('name');
            $table->tinyInteger('status');

            $table->softDeletes();
            $table->nullableTimestamps();
        });
    }

    public function down()
    {
        $this->dropIfExists('core_permissions');
    }
}
