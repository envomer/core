<?php

namespace Envo\Model;

use Envo\AbstractModel;

class QueryBuilder extends \Phalcon\Mvc\Model\Query\Builder
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
}