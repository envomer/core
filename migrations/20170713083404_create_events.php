<?php

use Envo\AbstractMigration;

class CreateEvents extends AbstractMigration
{
    public function up()
    {
    	$this->create('core_events', function(\Envo\Database\Migration\Table $table) {
    		$table->increments('id');
    		
    		$table->unsignedInteger('user_id')->index()->nullable();
    		$table->unsignedInteger('team_id')->index()->nullable();
    		
    		$table->string('reference', 64)->nullable();
    		$table->string('message')->nullable();
    		$table->text('data')->nullable();
    		
    		$table->unsignedInteger('event_type_id')->nullable();
    		$table->unsignedInteger('ip_id')->nullable();
    		$table->unsignedInteger('model_id')->nullable();
    		$table->unsignedInteger('model_entry_id')->nullable();
    		
    		$table->timestamp('created_at');
		});
    }

    public function down()
    {
        $this->dropIfExists('core_events');
    }
}
