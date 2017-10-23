<?php

use Envo\AbstractMigration;

class CreatePermissionRoles extends AbstractMigration
{
    public function up()
    {
        $this->create('core_permission_roles', function($table) {
            $table->increments('id');
            
            $table->string('name');
            $table->integer('team_id');
            $table->integer('user_id'); // creator

            $table->softDeletes();
            $table->nullableTimestamps();
        });
    }

    public function down()
    {
        $this->dropIfExists('core_permission_roles');
    }
}
