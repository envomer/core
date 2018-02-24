<?php

use Envo\AbstractMigration;
use Envo\Database\Migration\Table;

class CreateRules extends AbstractMigration
{
    public function up()
    {
        $this->create('core_rules', function(Table $table) {
            $table->integer('permission_id')->unsigned();
            $table->integer('role_id')->unsigned();
            $table->integer('module_id')->unsigned();
		});
        
        //$this->table('core_rules')->unique(['permission_id', 'role_id']);
    }

    public function down()
    {
        $this->dropIfExists('core_permission_roles');
    }
}
