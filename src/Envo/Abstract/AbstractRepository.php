<?php

namespace Envo;

use Envo\Model\QueryBuilder;

/**
 * Class AbstractRepository
 *
 * @package Envo
 *
 * @property AbstractModel model
 * @property QueryBuilder  builder
 */
class AbstractRepository
{
	/**
	 * @var AbstractModel|null
	 */
	protected $model;
	
	/**
	 * @var QueryBuilder
	 */
	protected $builder;
	
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