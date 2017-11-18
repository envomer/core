<?php

use Envo\AbstractMigration;
use Envo\Database\Migration\Table;

class CreateModules extends AbstractMigration
{
    public function up()
    {
        $this->create('core_modules', function(Table $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('slug');
            $table->integer('status');
	
			$table->softDeletes();
			$table->nullableTimestamps();
        });
    }

    public function down()
    {
        $this->dropIfExists('core_user_team');
    }
}
