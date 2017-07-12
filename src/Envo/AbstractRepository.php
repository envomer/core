<?php

namespace Envo;

class AbstractRepository
{
	protected $model = null;

	public function __construct(AbstractModel $model)
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
}