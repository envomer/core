<?php

namespace Envo;

use Envo\Model\Eagerload\EagerloadTrait;
use Envo\Support\Arr;
use Envo\Support\Validator;

class AbstractModel extends \Phalcon\Mvc\Model
{
	use EagerloadTrait;

	protected $softDeletes = false;
	protected $allowUpdate = true;
	protected $cachedRelations = [];
	
	/**
	 * @var AbstractRepository
	 */
	protected static $repo;
	
	/**
	 * @var AbstractService
	 */
	protected static $service;

	public $reference;

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
        if( ! $type ) {
			return '';
		}

        if( isset($this->{$type . 'Columns'}) ) {
			return $this->{$type . 'Columns'};
		}
        
		return '';
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
			if( ! isset($options['alias']) ) {
				continue;
			}
			if( $name && $name == $options['alias'] ) {
				return $relation;
			}
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

	/**
	 * Is deleteable
	 *
	 * @return boolean
	 */
	public function isDeletable()
	{
		return true;
	}

	/**
	 * Whether the model can be updated
	 *
	 * @param bool|null $choice
	 * @return bool
	 */
	public function allowUpdate($choice = null)
	{
		if( null === $choice ) {
			return $this->allowUpdate;
		}

		return $this->allowUpdate = $choice;
	}

	/**
	 * Json serialize model
	 *
	 * @return array
	 */
	public function jsonSerialize()
	{
		if( method_exists($this, 'toDTO') ) {
			return $this->toDTO();
		}
		
		return Arr::getPublicProperties($this);
	}

	/**
	 * Reference model relation
	 *
	 * @param string $name
	 * @param boolean $fresh
	 * @return void
	 */
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

	/**
	 * Get model repository
	 *
	 * @return AbstractRepository
	 */
	public static function repo()
	{
		if(self::$repo === null) {
			$repoClass = str_replace('\\Model\\', '\\Model\\Repository\\', static::class);
			
			// Model repository class does not exist.
			// Use the AbstractRepository class as fallback
			if(!class_exists($repoClass)) {
				$repoClass = AbstractRepository::class;
			}
			self::$repo = new $repoClass(new static);
		}

		return self::$repo;
	}

	/**
	 * Get model service
	 *
	 * @return AbstractService
	 */
	public static function service()
	{
		if(self::$service === null) {
			$serviceClass = str_replace('\\Model\\', '\\Service\\', static::class);
			
			// Model service class does not exist.
			// Use the AbstractService class as fallback
			if(!class_exists($serviceClass)) {
				$serviceClass = AbstractService::class;
			}
			self::$service = new $serviceClass(new static);
		}
		
		return self::$service;
	}

	/**
	 * Get id
	 *
	 * @return integer|null
	 */
	public function getId()
	{
		return isset($this->id) ? $this->id : null;
	}

	/**
	 * Create builder
	 *
	 * @param string $alias
	 * @return void
	 */
	public function createBuilder($alias = 'e')
	{
		$builder = $this->getModelsManager()->createBuilder();
		$builder->from([$alias => \get_class($this)]);

		return $builder;
	}
}
