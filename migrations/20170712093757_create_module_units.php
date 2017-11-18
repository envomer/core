<?php

use Envo\AbstractMigration;
use Envo\Database\Migration\Table;

class CreateModuleUnits extends AbstractMigration
{
    public function up()
    {
        $this->create('core_module_units', function(Table $table) {
			$table->increments('id');
			$table->string('name');
			$table->string('slug');
	
			$table->softDeletes();
			$table->nullableTimestamps();
        });
    }

    public function down()
    {
        $this->dropIfExists('core_module_units');
    }
}
