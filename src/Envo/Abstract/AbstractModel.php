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
	
	/**
	 * Cached related entities
	 *
	 * @var array
	 */
	protected $cachedRelations = [];
	
	/**
	 * @var AbstractRepository
	 */
	protected static $repos;
	
	/**
	 * @var AbstractService
	 */
	protected static $services;
	
	/**
	 * ???
	 *
	 * @var
	 */
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
		/** @var array $relations */
		$relations = $this->modelsManager->getRelations(get_class($this));
		$arr = [];
		foreach ($relations as $key => $relation) {
			$options = $relation->getOptions();
			if( ! isset($options['alias']) ) {
				continue;
			}
			if( $name && $name === $options['alias'] ) {
				return $relation;
			}
			$arr[$options['alias']] = $relation;
		}
		
		return $name ? null : $arr;
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
	 * Whether the model is soft deletable
	 * 
	 * @return bool
	 */
	public function isSoftDeletable()
	{
		return $this->softDeletes;
	}

	/**
	 * Is deletable
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
	 * @return mixed
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
		$className = static::class;
		
		if(!isset(self::$repos[$className])) {
			$parts = explode('\\', $className);
			
			array_splice($parts, count($parts) - 1, 0, 'Repository'); // splice in at position 3
			$repoClass = implode('\\', $parts);
			
			// Model repository class does not exist.
			// Use the AbstractRepository class as fallback
			if(!class_exists($repoClass)) {
				$repoClass = AbstractRepository::class;
			}
			
			self::$repos[$className] = new $repoClass(new $className);
		}

		return self::$repos[$className];
	}

	/**
	 * Get model service
	 *
	 * @return AbstractService
	 */
	public static function service()
	{
		$className = static::class;
		
		if(!isset(self::$services[$className])) {
			$serviceClass = str_replace('\\Model\\', '\\Service\\', static::class);
			
			// Model service class does not exist.
			// Use the AbstractService class as fallback
			if(!class_exists($serviceClass)) {
				$serviceClass = AbstractService::class;
			}
			self::$services[$className] = new $serviceClass(new $className);
		}
		
		return self::$services[$className];
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
