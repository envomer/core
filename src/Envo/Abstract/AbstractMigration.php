<?php

namespace Envo;

use Envo\Model\Migration\Table;

use Phinx\Migration\AbstractMigration as PhinxAbstractMigration;

class AbstractMigration extends PhinxAbstractMigration
{
    /**
     * Create table instance
     *
     * @param [type] $tableName
     * @param array $options
     * @return void
     */
    public function table($tableName, $options = array())
    {
        return new Table($tableName, $options, $this->getAdapter());
    }

    /**
     * Create table
     *
     * @param [type] $name
     * @param \Closure $closure
     * @return void
     */
    public function create($name, \Closure $closure)
    {
        $table = $this->table($name, ['id' => false, 'primary_key' => ['id']]);

        call_user_func($closure, $table);

        $table->create();
    }

    /**
     * Update table
     *
     * @param string $name
     * @param \Closure $closure
     * @return void
     */
    public function update($name, \Closure $closure)
    {
        $table = $this->table($name);

        call_user_func($closure, $table);

        $table->update();
    }

    /**
     * Drop table if exists
     *
     * @return void
     */
    public function dropIfExists($name)
    {
        if( $this->hasTable($name) ) {
            $this->dropTable($name);
        }
    }
}