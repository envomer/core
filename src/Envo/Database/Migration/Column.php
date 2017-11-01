<?php

namespace Envo\Database\Migration;
use Phalcon\Db\Index;

/**
 * Class Column
 * @package lib\Database
 */
class Column extends \Phalcon\Db\Column
{
	/**
	 * @var Table
	 */
	public $table;
	
	public function index($unique = false)
	{
		$this->table->indexes[] = new Index(
			$this->getName(),
			[$this->getName()],
			$unique ? 'UNIQUE' : null
		);
		
		return $this;
	}
	
	public function nullable()
	{
		$this->_notNull = false;
		
		return $this;
	}
	
	public function unique()
	{
		return $this->index(true);
	}
	
	public function unsigned()
	{
		$this->_unsigned = true;
		
		return $this;
	}

}