<?php

namespace Envo;

use Envo\Database\Migration\Table;
use Phalcon\Db\Adapter;
use Phalcon\Di;

class AbstractMigration
{
	protected $connection;
	
	/**
	 * AbstractMigration constructor.
	 *
	 * @param Adapter|null $connection
	 */
	public function __construct(Adapter $connection = null)
	{
		if(!$connection) {
			$connection = Di::getDefault()->get('db');
		}
		
		$this->connection = $connection;
	}
	
	/**
     * Create table instance
     *
     * @param string $tableName
     * @param array $options
     * @return Table
     */
    public function table($tableName, array $options = array())
    {
        return new Table($tableName, $options);
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
        $table = $this->table($name);

        $closure($table);
		
        $this->connection->createTable($table->name, null, [
        	'columns' => $table->columns
		]);
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

        $closure($table);

        $table->update();
    }
	
	/**
	 * Drop table if exists
	 *
	 * @param $name
	 *
	 * @return void
	 */
    public function dropIfExists($name)
    {
        if( $this->connection->tableExists($name) ) {
            $this->connection->dropTable($name);
        }
    }
	
	public function hasTable($name)
	{
		return $this->connection->tableExists($name);
	}
	
	public function dropTable($name)
	{
		return $this->connection->dropTable($name);
	}
}