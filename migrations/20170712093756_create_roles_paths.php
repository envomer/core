<?php

use Envo\AbstractMigration;
use Envo\Database\Migration\Table;

class CreateRolesPaths extends AbstractMigration
{
    public function up()
    {
        $this->create('core_roles_paths', function(Table $table) {
            $table->integer('parent_id')->unsigned();
            $table->integer('child_id')->unsigned();
            $table->integer('path_length')->unsigned();
        });
        
        //$this->table('core_roles_paths')->unique(['parent_id', 'child_id']);
    }

    public function down()
    {
        $this->dropIfExists('core_roles_paths');
    }
}
