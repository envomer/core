<?php

use Envo\AbstractMigration;
use Envo\Database\Migration\Table;

class CreatePermissions extends AbstractMigration
{
    public function up()
    {
        $this->create('core_permissions', function(Table $table) {
            $table->increments('id');
            $table->string('name');
			$table->integer('module_id')->unsigned()->nullable();
        });
        
        //$this->table('core_permissions')->unique(['name', 'module_unit_id']);
    }

    public function down()
    {
        $this->dropIfExists('core_permissions');
    }
}
