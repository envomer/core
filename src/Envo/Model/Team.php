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
	public $identifier;
	
	/**
	 * Team name
	 *
	 * @var string
	 */
	public $name;
	
	/**
	 * @var string
	 */
	public $created_at;
	
	/**
	 * @var string
	 */
	public $updated_at;
	
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