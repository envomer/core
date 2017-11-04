<?php

use Envo\AbstractMigration;

class CreateEventTypes extends AbstractMigration
{
    public function up()
    {
    	$this->create('core_event_types', function(\Envo\Database\Migration\Table $table) {
			$table->increments('id');
			
			$table->string('class');
			$table->timestamp('datetime');
		});
    }

    public function down()
    {
		$this->dropIfExists('core_event_types');
    }
}
