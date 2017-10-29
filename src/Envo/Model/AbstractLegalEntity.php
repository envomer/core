<?php

namespace Envo\Model;

use Envo\AbstractModel;
use Phalcon\Mvc\Model\MetaDataInterface;

abstract class AbstractLegalEntity extends AbstractModel
{
	/**
	 * @var LegalEntity|null
	 */
	protected $parent;
	
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
	 * get a name representation of this entity
	 *
	 * @return string
	 */
	abstract public function getName();
	
	/**
	 * @param LegalEntity $legalEntity
	 *
	 * @return AbstractLegalEntity
	 */
	abstract public function setParent(LegalEntity $legalEntity);
}