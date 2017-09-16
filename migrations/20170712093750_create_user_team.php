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
        });
    }

    public function down()
    {
        $this->dropIfExists('core_user_team');
    }
}
