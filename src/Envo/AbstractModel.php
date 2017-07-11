<?php

namespace Envo;

use Envo\Model\EagerloadTrait;

class AbstractModel extends \Phalcon\Mvc\Model
{
	use EagerloadTrait;

	protected $fillable = [];
	protected $softDeletes = false;
	protected $allowUpdate = true;
	protected $cachedRelations = [];

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
        if( isset($this->{$type . 'Columns'}) ) return $this->{$type . 'Columns'};
        else return '';
    }

	/**
	* Override model values
	*/
	public function override(array $data, $merge = true, $fillableType = null)
	{
		$model = $this;
		$sub = false;
		$newSub = false;

		if( isset($data['fill']) && $fillableType != 'save' ) {
			$relation = $this->getRelations($data['fill']);
			if( ! $relation ) return _t('app.invalid');

			$relationName = $relation->getReferencedModel();
			$fullModel = $this->load($data['fill']);
			if( ! $fullModel ) return _t('app.invalid');

			if( ! $fullModel->$data['fill'] ) {
				$model = new $relationName;
				$newSub = true;
			}
			else $model = $fullModel->$data['fill'];
			$sub = true;
		}

		/**
		 * store arrays as json encodes
		 */
		$fillables = $model->getFillable(null, $fillableType);
		if( ! $fillables ) return _t('app.invalid'); // call event maybe?

		foreach($fillables as $fillable) {
			if( ! array_key_exists($fillable, $data) ) continue;

			$value = isset($data[$fillable]) ? $data[$fillable] : null;
			if( isset($data[$fillable]) && is_bool($data[$fillable]) ) $value = $data[$fillable] ? 1 : 0;

			if( ! is_array($data[$fillable]) ) $model->$fillable = $value;
			else {
				$content = isset($model->$fillable) ? $model->$fillable : [];
				if( $merge && isset($value) && is_array($this->$fillable) ) {
					$content = array_merge($content, $value);
				} else {
					$content = $value;
				}
				$model->$fillable = json_encode($content, JSON_UNESCAPED_UNICODE);
			}
		}

		if( $sub ) {
			$model->{$relation->getReferencedFields()} = $this->{$relation->getFields()};
			if( ! $model->save() ) {
				if( $msgs = $entry->getMessages() ) return $msgs;
		    	return false;
			}
			$this->$data['fill'] = $model;
		}

		return $this;
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
		return new \Validator;
	}

	/**
	 * Get the fillable attributes
	 * 
	 * @param  string $name
	 * @return array
	 */
	public function getFillable($name = null, $type = null)
	{
		if( ! isset($this->fillable) ) return false;

		// if type isset, then l
		$fillables = $this->fillable;
		if( $type && isset($fillables[$type]) ) $fillables = $fillables[$type];
		
		if( ! $name ) return $fillables;
		if( in_array($name, $fillables) !== false ) return true;
		return $fillables[$name];
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

	public static function withOld($args)
	{
		$with = $args;
		if( isset($args['with']) ) {
			$with = $args['with'];
			// unset($args['with']);
		}
		// $with = (isset($args['with'])) ? $args['with'] : $args;
		// die(var_dump($args));
		$elements = self::find($args);

		if( ! $elements ) return $elements;

		$result = $elements;
		foreach( $with as $withKey ) {
			$result = \Lazyload::with($result, $withKey);
		}
		die(var_dump($result));
		// $elements = \Lazyload::with()
		die(var_dump($args));
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
		return \Arr::getPublicProperties($this);
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
}
