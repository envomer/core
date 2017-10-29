<?php

namespace Envo\Model;

/**
 * Class Team
 *
 * @package Envo\Model
 *
 * @property integer id
 * @property string  identifier
 * @property string  name
 */
class Team extends AbstractLegalEntity
{
	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $table = 'core_teams';
	
	/**
	 * @var integer
	 */
	protected $id;
	
	/**
	 * Identifier of user
	 *
	 * @var string
	 */
	protected $identifier;
	
	/**
	 * Username
	 *
	 * @var string
	 */
	protected $name;
	
	/**
	 * get a name representation of this entity
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}
	
	/**
	 * @param LegalEntity $legalEntity
	 *
	 * @return AbstractLegalEntity
	 */
	public function setParent( LegalEntity $legalEntity )
	{
		$this->parent = $legalEntity;
		
		return $this;
	}
}