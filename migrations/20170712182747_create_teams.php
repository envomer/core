<?php

use Phinx\Migration\AbstractMigration;

class CreateTeams extends AbstractMigration
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
     *
     *
     *
     *
     * Teams are just like Teams. When a user decides to enable access to more users
     * a team needs to be created and the new user must have the team_id set
     *
     */
    public function up()
    {
        $users = $this->table('core_teams', [
            'id' => false,
            'primary_key' => 'id'
        ]);

        $users->addColumn('id', 'integer', ['signed' => false, 'identity' => true]);
        $users->addColumn('name', 'string');

        /** user id of owner **/
        $users->addColumn('owner_id', 'integer', ['signed' => false]);

        $users->addColumn('created_at', 'timestamp', ['null' => true]);
        $users->addColumn('deleted_at', 'timestamp', ['null' => true]);
        $users->addColumn('updated_at', 'timestamp', ['null' => true]);

        $users->create();
    }

    public function down()
    {
        if( $this->hasTable('core_teams') ) {
            $this->dropTable('core_teams');
        }
    }
}
