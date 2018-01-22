<?php

namespace Envo\Model\Traits;

use Envo\Model\Role;
use Phalcon\Mvc\Model\MetaDataInterface;

/**
 * Trait LegalEntityTrait
 * @package Envo\Model
 */
trait RoleTrait
{
	/**
	 * @param MetaDataInterface $metaData
	 * @param bool              $exists
	 * @param mixed             $identityField
	 *
	 * @return bool
	 */
	protected function _preSave( MetaDataInterface $metaData, $exists, $identityField )
	{
		if(! $exists){
			/* create a new legal entity if it is a new model */
			$legalEntity = new Role();
			$legalEntity->name   = $this->getName();
			$legalEntity->type   = str_replace('\\', '_' , static::class);
			$legalEntity->parent = $this->parent;
			$legalEntity->save();
			
			$this->$identityField = $legalEntity->{$legalEntity->getModelsMetaData()->getIdentityField($legalEntity)};
		}
		
		return true;
	}
	
	/**
	 * @param Role $child
	 */
	public function addChild( Role $child)
	{
		//todo implement me
	}
	
	/**
	 * @param Role $parent
	 */
	public function addParent( Role $parent)
	{
		//todo implement me
	}
	
	/**
	 * @param Role $child
	 */
	public function removeChild( Role $child)
	{
		//todo implement me
	}
	
	/**
	 * @param Role $parent
	 */
	public function removeParent( Role $parent)
	{
		//todo implement me
	}
	
	/**
	 * get a name representation of this entity
	 *
	 * @return string
	 */
	abstract public function getName();
}