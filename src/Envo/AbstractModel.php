<?php

namespace Envo;

use Envo\Model\EagerloadTrait;
use Envo\Support\Arr;
use Envo\Support\Validator;

class AbstractModel extends \Phalcon\Mvc\Model
{
	use EagerloadTrait;

	protected $softDeletes = false;
	protected $allowUpdate = true;
	protected $cachedRelations = [];
	// private $_justCreated = false;

	/**
	* Get the table name
	*
	* @return string table name
	*/
	public function getSource()
	{
		return $this->table;
	}
	
    /**
    * Get the columns of the table
    * 
    * @param  string $type name of the column
    * @return string       column value
    */
    public function getColumns($type = null)
    {
        if( ! $type ) return '';
        if( isset($this->{$type . 'Columns'}) ) {
			return $this->{$type . 'Columns'};
		}
        else return '';
    }

	/**
	 * Get the model relationships
	 * 
	 * @param  string $name
	 * @return array|null
	 */
	public function getRelations($name = null)
	{
		$relations = $this->modelsManager->getRelations(get_class($this));
		$arr = [];
		foreach ($relations as $key => $relation) {
			$options = $relation->getOptions();
			if( ! isset($options['alias']) ) continue;
			if( $name && $name == $options['alias'] ) return $relation;
			$arr[$options['alias']] = $relation;
		}
		return ($name) ? null : $arr;
	}

	/**
	 * Override the validate method of the model
	 * 
	 * @param  array $data
	 * @param  string $rules
	 * @return array|bool
	 */
	public function runValidation($data, $rules = 'rules')
	{
		return new Validator;
	}

	/**
	 * Wheter the model is soft deletable
	 * 
	 * @return bool
	 */
	public function isSoftDeletable()
	{
		return $this->softDeletes;
	}

	public function isDeletable()
	{
		return true;
	}

	public function allowUpdate($choice = null)
	{
		if( is_null($choice) ) return $this->allowUpdate;
		return $this->allowUpdate = $choice;
	}

	public function jsonSerialize() {
		if( method_exists($this, 'toDTO') ) {
			return $this->toDTO();
		}
		return Arr::getPublicProperties($this);
	}

	public function ref($name, $fresh = false)
	{
		if( is_bool($fresh) && $fresh ) {
			return $this->$name;
		}

		if( $fresh ) {
			return $this->cachedRelations[$name] = $fresh;
		}

		if( ! isset($this->cachedRelations[$name]) ) {
			return $this->cachedRelations[$name] = $this->$name;
		}
		
		return $this->cachedRelations[$name];
	}

	public static function repo()
	{
		$repoName = get_called_class() . 'Repository';

		return resolve($repoName);
	}

	public static function service()
	{
		$repoName = get_called_class() . 'Service';
		$repoName = str_replace('\\Model\\', '\\Service\\', $repoName);

		if( ! class_exists($repoName) ) {
			return false;
		}

		return resolve($repoName);
	}

	public function getId()
	{
		return isset($this->id) ? $this->id : null;
	}

	// public function setJustCreated($value)
	// {
	// 	$this->_justCreated = $value;
	// }

	// public function justCreated()
	// {
	// 	return $this->_justCreated;
	// }
}
