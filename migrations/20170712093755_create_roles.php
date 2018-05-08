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
			$table->string('role_id');
            $table->string('type');
            $table->integer('parent_id', false, true);
        });
    }

    public function down()
    {
        $this->dropIfExists('core_roles');
    }
}
