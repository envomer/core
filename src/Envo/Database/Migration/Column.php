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

}