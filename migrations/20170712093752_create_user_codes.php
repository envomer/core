<?php

use Envo\AbstractMigration;

class CreateUserClient extends AbstractMigration
{
    public function up()
    {
        $this->create('core_user_codes', function($table) {
            $table->increments('id');

            $table->integer('user_id')->unsigned()->index();
            $table->integer('team_id')->unsigned()->nullable()->index();
            $table->string('code', 64)->nullable();
            $table->boolean('status')->nullable();
            $table->integer('ip_id')->unsigned()->nullable();
            $table->smallInteger('type')->unsigned()->nullable();

            $table->timestamp('expires_at')->nullable();
            
            $table->timestamps();
        });
    }

    public function down()
    {
        $this->dropIfExists('core_user_codes');
    }
}
