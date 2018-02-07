<?php

namespace Envo\Model\Traits;

use Envo\Model\Role;
use Envo\Model\RoleRelation;
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
	protected function _postSave( MetaDataInterface $metaData, $exists, $identityField )
	{
		if(! $exists){
			/* create a new role if it is a new model */
			$role = new Role();
			$role->name   = $this->getName();
			$role->type   = str_replace('\\', '_' , static::class);
			$role->id     = $this->$identityField;
			$role->save();
		}
		
		return true;
	}
	
	/**
	 * @param Role $child
	 */
	public function addChild( Role $child)
	{
		$relation = new RoleRelation();
		$relation->child = $child;
		$relation->parent = $this;
		$relation->save();
	}
	
	/**
	 * @param Role $parent
	 */
	public function addParent( Role $parent)
	{
		$relation = new RoleRelation();
		$relation->child = $this;
		$relation->parent = $parent;
		$relation->save();
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
	abstract public function getName() :string ;
}