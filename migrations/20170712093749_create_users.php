<?php

use Phinx\Migration\AbstractMigration;

class CreateUsers extends AbstractMigration
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
        $users = $this->table('core_users', [
            'id' => false,
            'primary_key' => 'id'
        ]);

        $users->addColumn('id', 'integer', ['signed' => false, 'identity' => true]);
        $users->addColumn('identifier', 'string', ['limit' => 64]);
        $users->addColumn('username', 'string', ['limit' => 64]);
        $users->addColumn('email', 'string', ['limit' => 160]);
        $users->addColumn('password', 'string', ['limit' => 64]);
        $users->addColumn('client_id', 'integer', ['signed' => false, 'null' => true]);

        $users->addColumn('activated_at', 'timestamp', ['null' => true]);
        $users->addColumn('created_at', 'timestamp', ['null' => true]);
        $users->addColumn('deleted_at', 'timestamp', ['null' => true]);
        $users->addColumn('updated_at', 'timestamp', ['null' => true]);

        /** two factor auth **/
        $users->addColumn('tfa', 'boolean', ['null' => true]);

        $users->addColumn('is_online', 'boolean', ['null' => true]);
        $users->addColumn('api_key', 'string', ['limit' => 128]);

        $users->addIndex('email', ['unique' => true]);
        $users->addIndex(['username', 'email', 'identifier', 'api_key'], ['unique' => true]);

        $users->create();
    }

    public function down()
    {
        if( $this->hasTable('core_users') ) {
            $this->dropTable('core_users');
        }
    }
}
