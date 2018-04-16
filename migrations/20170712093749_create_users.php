<?php

use Envo\AbstractMigration;

use Envo\Database\Migration\Table;

class CreateUsers extends AbstractMigration
{
    public function up()
    {
        $this->create('core_users', function(Table $table) {
            $table->increments('id');

            $table->string('identifier', 64);
            $table->string('username', 64)->index();
            $table->string('email', 160)->unique()->index();
            
            $table->string('password', 64)->nullable();
            
            $table->integer('team_id')->unsigned()->nullable()->index(); // hmmm....
            $table->tinyInteger('level')->nullable();
            $table->tinyInteger('status')->nullable();

            $table->string('api_key', 128)->nullable();

            $table->boolean('tfa')->nullable();
            $table->boolean('is_online')->nullable();
            
            $table->timestamp('activated_at');

            $table->softDeletes();
            $table->nullableTimestamps();
        });
    }

    public function down()
    {
        $this->dropIfExists('core_users');
    }
}
