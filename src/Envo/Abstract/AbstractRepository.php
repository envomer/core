<?php

namespace Envo;

use Envo\Model\QueryBuilder;
use Phalcon\Di;

/**
 * Class AbstractRepository
 *
 * @package Envo
 *
 * @property AbstractModel model
 */
class AbstractRepository
{
	/**
	 * @var AbstractModel|null
	 */
	protected $model;
	
	/**
	 * AbstractRepository constructor.
	 *
	 * @param AbstractModel|null $model
	 */
	public function __construct(AbstractModel $model)
	{
		$this->model = $model;
	}
	
	/**
	 * @return QueryBuilder
	 */
	public function createBuilder()
	{
		$class = get_class($this->model);
		$queryBuilder = new QueryBuilder();
		$queryBuilder->from([
			lcfirst($class[0]) => $class
		]);
		
		return $queryBuilder;
	}
	
	/**
	 * @return \Phalcon\Mvc\Model\Resultset\Simple|\Phalcon\Mvc\Model\ResultsetInterface
	 */
	public function getAll()
	{
		$model = $this->model;
		
		return $model::find();
	}
	
	/**
	 * Where in
	 *
	 * @param       $key
	 * @param array $value
	 *
	 * @return QueryBuilder
	 */
	public function in($key, array $value)
	{
		$builder = $this->createBuilder();
		$builder->inWhere($key, $value);
		
		return $builder;
	}
	
	/**
	 * Where not in
	 *
	 * @param       $key
	 * @param array $value
	 *
	 * @return QueryBuilder
	 */
	public function notIn($key, array $value)
	{
		$builder = $this->createBuilder();
		$builder->notInWhere($key, $value);
		
		return $builder;
	}
	
	/**
	 * Get all results paginated
	 *
	 * @param     $page
	 * @param int $limit
	 *
	 * @return QueryBuilder
	 */
	public function page($page, $limit = 50)
	{
		$builder = $this->createBuilder();
		$builder->limit($limit);
		$builder->offset(($page - 1) * $limit);
		
		return $builder;
	}
	
	/**
	 * @param $statement
	 * @param $bindings
	 * @param $type
	 *
	 * @return bool|\Phalcon\Db\ResultInterface
	 */
	public function raw($statement, $bindings = null, $type = null)
	{
		/** @var \Phalcon\Db\Adapter\Pdo $db */
		$db = Di::getDefault()->get('db');
		
		return $db->query($statement, $bindings, $type);
	}
	
	/**
	 * @param      $model
	 * @param bool $override
	 *
	 * @return $this
	 */
	public function setModel($model, $override = false)
	{
		if(!$this->model || $override) {
			$this->model = new $model;
		}

		return $this;
	}
	
	/**
	 * @param $name
	 * @param $value
	 *
	 * @return QueryBuilder
	 */
	public function where($name, $value)
	{
		$builder = $this->createBuilder();
		$builder->where($name, $value);
		
		return $builder;
	}
}