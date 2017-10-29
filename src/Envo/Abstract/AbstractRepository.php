<?php

namespace Envo;

class AbstractRepository
{
	protected $model = null;

	protected static $modelsManager = null;

	public function __construct(AbstractModel $model = null)
	{
		$this->model = $model;
	}

	public function __call($name, $arguments)
	{
		if( strpos($name, 'getBy') !== false ) {
			$name = str_replace('getBy', '', $name);
			return $this->model->__call('findFirstBy' . $name, $arguments);
		}

		if( strpos($name, 'getAllBy') !== false ) {
			$name = str_replace('getAllBy', '', $name);
			return $this->model->__call('findBy' . $name, $arguments);
		}
		
		return $this->model->__call($name, $arguments);
	}

	public function setModel($model, $override = false)
	{
		if(!$this->model || $override) {
			$this->model = new $model;
		}

		return $this;
	}

	/**
	 * @param null $table
	 *
	 * @return \Phalcon\Mvc\Model\Query\Builder
	 */
	public static function getQueryBuilder($table = null, $alias = null)
	{
		$builder = self::modelsManager()->createBuilder();
		if( $table ) {
			if( $alias ) {
				$builder->from([$alias => $table]);
			}
			else {
				$builder->from($table);
			}
		}
		return $builder;
	}
	
	/**
	 * @return null|\Phalcon\Mvc\Model\ManagerInterface
	 */
	public static function modelsManager()
	{
		if( self::$modelsManager ) {
			return self::$modelsManager;
		}
		return self::$modelsManager = \Phalcon\Di::getDefault()->getModelsManager();
	}

	public static function raw($query, $params = null)
	{
		return self::modelsManager()->executeQuery($query, $params);
	}

	public static function getAllByProperty($property, $value, $whereIn = false)
	{
		$select = self::getQueryBuilder(self::model(), 'i');

        $bind = [
            $property => $value,
        ];
		if( $whereIn ) {
			$select->where($property . ' IN ({' . $property .':array})', $bind);
		} else {
    		$select->where($property . ' = :' . $property .':', $bind);
		}

        $query = $select->getQuery();

        return $query->execute();
	}

	public static function getByProperty($property, $value)
	{
		$model = self::model();
		return $model::findFirst([
			'conditions' => $property . ' = :' . $property . ':',
			'bind' => [$property => $value]
		]);
	}
}