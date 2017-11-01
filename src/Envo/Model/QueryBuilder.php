<?php

namespace Envo\Model;

use Envo\AbstractModel;
use Phalcon\Mvc\Model\Query\Builder;

class QueryBuilder extends Builder
{
	/**
	 * @return \Phalcon\Mvc\Model\Resultset\Simple
	 */
	public function get()
	{
		return $this->getQuery()->execute();
	}
	
	/**
	 * @return AbstractModel|null
	 */
	public function getOne()
	{
		return $this->getQuery()->getSingleResult();
	}
	
	
	public function cache($time, $key = null)
	{
	
	}
}