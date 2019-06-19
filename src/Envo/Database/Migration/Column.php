<?php

namespace Envo\Database\Migration;
use Phalcon\Db\Index;

/**
 * Class Column
 * @package lib\Database
 * @method default($value)
 */
class Column extends \Phalcon\Db\Column
{
	/**
	 * @var Table
	 */
	public $table;
	
	public $toBeRemoved = false;
	
	public $toBeChanged = false;
	
	/**
	 * @param bool $unique
	 *
	 * @return $this
	 */
	public function index($unique = false)
	{
		$this->table->indexes[] = new Index(
			$this->getName(),
			[$this->getName()],
			$unique ? 'UNIQUE' : null
		);
		
		return $this;
	}
	
	/**
	 * @return $this
	 */
	public function nullable()
	{
		$this->_notNull = false;
		
		return $this;
	}
	
	/**
	 * @return Column
	 */
	public function unique()
	{
		return $this->index(true);
	}
	
	/**
	 * @return $this
	 */
	public function unsigned()
	{
		$this->_unsigned = true;
		
		return $this;
	}
	
	/**
	 * @param $columnName
	 *
	 * @return $this
	 */
	public function after($columnName)
	{
		$this->_after = $columnName;
		
		return $this;
	}
	
	/**
	 * @param bool $set
	 *
	 * @return $this
	 */
	public function primary($set = true)
	{
		$this->_primary = $set;
		
		return $this;
	}
	
	/**
	 * @param bool $set
	 *
	 * @return $this
	 */
	public function first($set = true)
	{
		$this->_first = $set;
		
		return $this;
	}
	
	/**
	 * @param bool $set
	 *
	 * @return $this
	 */
	public function autoIncrement($set = true)
	{
		$this->_autoIncrement = $set;
		
		return $this;
	}
	
	/**
	 * @param $name
	 * @param $value
	 *
	 * @return $this
	 */
	public function __call($name, $value)
	{
		if($name === 'default') {
			$this->_default = reset($value);
		}
		
		return $this;
	}
	
	public function change()
	{
		$this->toBeChanged = true;
	}

}