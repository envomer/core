<?php

use Envo\AbstractMigration;
use Envo\Database\Migration\Table;

class CreateLegalEntites extends AbstractMigration
{
    public function up()
    {
        $this->create('core_legal_entities', function(Table $table) {
            $table->increments('id');
            
            $table->string('name');

            $table->softDeletes();
            $table->nullableTimestamps();
        });
    }

    public function down()
    {
        $this->dropIfExists('core_permissions');
    }
}
