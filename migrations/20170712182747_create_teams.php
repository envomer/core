<?php

use Envo\AbstractMigration;

class CreateTeams extends AbstractMigration
{
    public function up()
    {
        $this->create('core_teams', function($table) {
            $table->increments('id');

            $table->string('identifier', 32)->nullable()->index();

            $table->string('name');
            $table->timestamps();
            $table->timestamp('deleted_at');
        });
    }

    public function down()
    {
        $this->dropIfExists('core_teams');
    }
}
