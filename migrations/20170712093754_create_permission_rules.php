<?php

use Envo\AbstractMigration;
use Envo\Database\Migration\Table;

class CreatePermissionRules extends AbstractMigration
{
    public function up()
    {
        $this->create('core_permission_rules', function(Table $table) {
            $table->increments('id');
            
            $table->integer('team_id')->unsigned();
            $table->integer('permission')->unsigned();
            $table->integer('module_unit_id')->unsigned();

            $table->softDeletes();
            $table->nullableTimestamps();
        });
    }

    public function down()
    {
        $this->dropIfExists('core_permission_rules');
    }
}
