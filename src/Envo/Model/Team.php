<?php

namespace Envo\Model;
use Envo\AbstractRole;

/**
 * Class Team
 *
 * @package Envo\Model
 *
 * @property integer id
 * @property string  identifier
 * @property string  name
 */
class Team extends AbstractRole
{
	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $table = 'core_teams';
	
	/**
	 * Identifier of team
	 *
	 * @var string
	 */
	protected $identifier;
	
	/**
	 * Team name
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
}