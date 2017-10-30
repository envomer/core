<?php

namespace Envo\Model;

use Phalcon\Mvc\Model\MetaDataInterface;

/**
 * Trait LegalEntityTrait
 * @package Envo\Model
 */
trait LegalEntityTrait
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
			$legalEntity = new LegalEntity();
			$legalEntity->name   = $this->getName();
			$legalEntity->type   = str_replace('\\', '_' , static::class);
			$legalEntity->parent = $this->parent;
			$legalEntity->save();
			
			$this->$identityField = $legalEntity->{$legalEntity->getModelsMetaData()->getIdentityField($legalEntity)};
		}
		
		return true;
	}
	
	/**
	 * @param LegalEntity $child
	 */
	public function addChild( LegalEntity $child)
	{
		//todo implement me
	}
	
	/**
	 * @param LegalEntity $parent
	 */
	public function addParent( LegalEntity $parent)
	{
		//todo implement me
	}
	
	/**
	 * @param LegalEntity $child
	 */
	public function removeChild( LegalEntity $child)
	{
		//todo implement me
	}
	
	/**
	 * @param LegalEntity $parent
	 */
	public function removeParent( LegalEntity $parent)
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