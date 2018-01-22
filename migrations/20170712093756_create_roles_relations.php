<?php

use Envo\AbstractMigration;
use Envo\Database\Migration\Table;

class CreateRolesRelations extends AbstractMigration
{
    public function up()
    {
        $this->create('core_roles_relation', function(Table $table) {
            $table->integer('parent_id')->unsigned();
            $table->integer('child_id')->unsigned();
        });
        
        $this->table('core_roles_relation')->unique(['parent_id', 'child_id']);
    }

    public function down()
    {
        $this->dropIfExists('core_legal_entities_self');
    }
}
