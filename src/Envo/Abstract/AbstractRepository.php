<?php

namespace Envo;

use Envo\Model\QueryBuilder;

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
		return (new QueryBuilder())->from([
			lcfirst($class[0]) => $class
		]);
	}
	
	/**
	 * @return \Phalcon\Mvc\Model\Resultset\Simple
	 */
	public function getAll()
	{
		return $this->model->find();
	}
	
	/**
	 * Where in
	 */
	public function in($key, array $value)
	{
		$builder = $this->createBuilder();
		$builder->inWhere($key, $value);
		return $builder;
	}
	
	/**
	 * Where not in
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