<?php

use Envo\AbstractMigration;

class CreateUserRoles extends AbstractMigration
{
    public function up()
    {
        $this->create('core_user_roles', function($table) {
            $table->increments('id');
            
            $table->integer('user_id')->unsigned();
            $table->integer('role_id')->unsigned();

            $table->softDeletes();
            $table->nullableTimestamps();
        });
    }

    public function down()
    {
        $this->dropIfExists('core_user_roles');
    }
}
