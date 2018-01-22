<?php

use Envo\AbstractMigration;
use Envo\Database\Migration\Table;

class CreateRoles extends AbstractMigration
{
    public function up()
    {
        $this->create('core_roles', function(Table $table) {
            $table->increments('id');
            
            $table->string('name');
            $table->string('type');
        });
	
		$this->table('core_roles')->unique(['name', 'type']);
    }

    public function down()
    {
        $this->dropIfExists('core_roles');
    }
}
