<?php

use Envo\AbstractMigration;
use Envo\Database\Migration\Table;

class CreateRoles extends AbstractMigration
{
    public function up()
    {
        $this->create('core_roles', function(Table $table) {
            $table->increments('id');
            
            $table->string('name')->nullable();
			$table->integer('role_id',false, true);
            $table->string('type');
            $table->integer('parent_id', false, true);
        });
    }

    public function down()
    {
        $this->dropIfExists('core_roles');
    }
}
