<?php

use Phinx\Migration\AbstractMigration;

class CreateEventTypes extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function up()
    {
        $table = $this->table('core_event_types', [
            'id' => false,
            'primary_key' => 'id'
        ]);

        $table->addColumn('id', 'integer', ['signed' => false, 'identity' => true]);

        $table->addColumn('class', 'string');

        $table->addColumn('created_at', 'timestamp', ['null' => true]);

        $table->create();
    }

    public function down()
    {
        if( $this->hasTable('core_event_types') ) {
            $this->dropTable('core_event_types');
        }
    }
}
