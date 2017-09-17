<?php

use Envo\AbstractMigration;

class CreateUserTeam extends AbstractMigration
{
    public function up()
    {
        $this->create('core_user_team', function($table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->index();
            $table->integer('team_id')->unsigned()->index();

            // simple rights system (1: owner, 2: read, 3: write, admin: 9)
            $table->smallInteger('rights')->nullable();

            // for a better permission system, create a permission/role system
        });
    }

    public function down()
    {
        $this->dropIfExists('core_user_team');
    }
}
