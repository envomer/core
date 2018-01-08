<?php

namespace Envo;

use Envo\Model\QueryBuilder;
use Phalcon\Di;
use Phalcon\Mvc\Model;

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
	 * @param Model $model
	 */
	public function __construct(Model $model = null)
	{
		$this->model = $model;
	}
	
	/**
	 * @param null $alias
	 *
	 * @return QueryBuilder
	 */
	public function createBuilder($alias = null)
	{
		$className = get_class($this->model);
		$alias = $alias ?: strtolower(substr($className, strrpos($className, '\\') + 1)[0]);
		
		$queryBuilder = new QueryBuilder();
		$queryBuilder->from([
			$alias => $className
		]);
		
		return $queryBuilder;
	}
	
	/**
	 * @param $statement
	 * @param $bindings
	 * @param $type
	 *
	 * @return bool|\Phalcon\Db\ResultInterface
	 */
	public function execute($statement, $bindings = null, $type = null)
	{
		/** @var \Phalcon\Db\Adapter\Pdo $db */
		$db = Di::getDefault()->get('db');
		
		return $db->execute($statement, $bindings, $type);
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
	public function query($statement, $bindings = null, $type = null)
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
		if(strpos($name, ' ', true)) {
			$builder->where($name, $value);
		} else {
			$builder->where($name .' = :'.$name.':', [
				$name => $value
			]);
		}
		
		return $builder;
	}
}