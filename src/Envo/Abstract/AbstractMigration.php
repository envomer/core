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
    public function table($tableName, $options = null)
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
        	'columns' => $table->columns,
            'indexes' => $table->indexes
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
	
		foreach ($table->columns as $column) {
			if($column->toBeChanged) {
				$this->connection->modifyColumn($table->name, null, $column);
			} else if($column->toBeRemoved) {
				$this->connection->dropColumn($table->name, null, $column->getName());
			} else {
				$this->connection->addColumn($table->name, null, $column);
			}
        }
    
        foreach ($table->indexes as $index){
            $this->connection->addIndex($table->name, null, $index);
        }
        //$table->update();
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
