<?php

use Phinx\Migration\AbstractMigration;

class CreateIps extends AbstractMigration
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
        $table = $this->table('core_ips', [
            'id' => false,
            'primary_key' => 'id'
        ]);

        $table->addColumn('id', 'integer', ['signed' => false, 'identity' => true]);

        $table->addColumn('ip', 'string', ['length' => 64]);
        $table->addColumn('country', 'string', ['length' => 64, 'null' => true]);
        $table->addColumn('country_code', 'string', ['length' => 64, 'null' => true]);
        $table->addColumn('region', 'string', ['length' => 64, 'null' => true]);
        $table->addColumn('city', 'string', ['length' => 64, 'null' => true]);
        $table->addColumn('zip', 'string', ['length' => 64, 'null' => true]);
        $table->addColumn('lat', 'string', ['length' => 64, 'null' => true]);
        $table->addColumn('lon', 'string', ['length' => 64, 'null' => true]);
        $table->addColumn('timezone', 'string', ['length' => 64, 'null' => true]);
        $table->addColumn('isp', 'string', ['null' => true]);
        $table->addColumn('org', 'string', ['null' => true]);
        $table->addColumn('user_id', 'integer', ['signed' => false, 'null' => true]);
        $table->addColumn('status', 'integer', ['null' => true]);

        $table->addColumn('created_at', 'timestamp', ['null' => true]);

        $table->addIndex('ip', ['unique' => true]);

        $table->create();
    }

    public function down()
    {
        if( $this->hasTable('core_ips') ) {
            $this->dropTable('core_ips');
        }
    }
}
