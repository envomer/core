<?php

use Envo\AbstractMigration;

class CreateUserClient extends AbstractMigration
{
    public function up()
    {
        $this->create('core_user_client', function($table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->index();
            $table->integer('client_id')->unsigned()->index();
        });
    }

    public function down()
    {
        $this->dropIfExists('core_user_client');
    }
}
