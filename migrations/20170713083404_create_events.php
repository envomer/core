<?php

use Phinx\Migration\AbstractMigration;

class CreateEvents extends AbstractMigration
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
        $table = $this->table('core_events', [
            'id' => false,
            'primary_key' => 'id'
        ]);

        $table->addColumn('id', 'integer', ['signed' => false, 'identity' => true]);
        $table->addColumn('user_id', 'integer', ['signed' => false]);
        $table->addColumn('client_id', 'integer', ['signed' => false]);

        $table->addColumn('message', 'string');
        $table->addColumn('data', 'text');

        $table->addColumn('event_type_id', 'integer', ['signed' => false]);
        $table->addColumn('ip_id', 'integer', ['signed' => false]);

        $table->addColumn('model_id', 'integer', ['signed' => false]);
        $table->addColumn('model_entry_id', 'integer', ['signed' => false]);

        $table->addColumn('created_at', 'timestamp', ['null' => true]);

        $table->create();
    }

    public function down()
    {
        if( $this->hasTable('core_events') ) {
            $this->dropTable('core_events');
        }
    }
}
