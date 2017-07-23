<?php

use Envo\AbstractMigration;

class CreateUserClient extends AbstractMigration
{
    public function up()
    {
        $this->create('core_failed_logins', function($table) {
            $table->increments('id');

            $table->integer('user_id')->unsigned()->index();
            $table->string('ip', 32)->nullable();
            $table->integer('attempted')->unsigned();
            
            $table->timestamps();
        });
    }

    public function down()
    {
        $this->dropIfExists('core_failed_logins');
    }
}
