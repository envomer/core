<?php

use Envo\AbstractMigration;

class CreatePermissionRules extends AbstractMigration
{
    public function up()
    {
        $this->create('core_permission_rules', function($table) {
            $table->increments('id');
            
            $table->integer('role_id')->unsigned();
            $table->integer('permission_id')->unsigned();

            $table->softDeletes();
            $table->nullableTimestamps();
        });
    }

    public function down()
    {
        $this->dropIfExists('core_permission_rules');
    }
}
