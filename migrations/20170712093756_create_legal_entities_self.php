<?php

use Envo\AbstractMigration;
use Envo\Database\Migration\Table;

class CreateLegalEntitiesSelf extends AbstractMigration
{
    public function up()
    {
        $this->create('core_legal_entities_self', function(Table $table) {
            $table->integer('parent_id')->unsigned();
            $table->integer('child_id')->unsigned();
        });
    }

    public function down()
    {
        $this->dropIfExists('core_legal_entities_self');
    }
}
