<?php

namespace Envo\Model;
use Envo\AbstractModel;

/**
 * Class Team
 *
 * @package Envo\Model
 *
 * @property integer id
 * @property string  identifier
 * @property string  name
 */
class Team extends AbstractModel
{
	use LegalEntityTrait;
	
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