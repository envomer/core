<?php

use Envo\AbstractMigration;

class CreateEventModels extends AbstractMigration
{
    public function up()
    {
    	$this->create('core_event_models', function(\Envo\Database\Migration\Table $table) {
    		$table->increments('id');
    		
    		$table->string('class');
    		$table->timestamp('created_at');
		});
    }

    public function down()
    {
		$this->dropIfExists('core_event_models');
    }
}
