<?php

use Envo\AbstractMigration;
use Envo\Database\Migration\Table;

class CreatePermissionRoles extends AbstractMigration
{
    public function up()
    {
        $this->create('core_permission_roles', function(Table $table) {
            $table->integer('team_id');
            $table->integer('permission_rule_id');
        });
    }

    public function down()
    {
        $this->dropIfExists('core_permission_roles');
    }
}
