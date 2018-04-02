<?php

namespace Envo\Model;

use Envo\AbstractModel;

/**
 * Class Role
 *
 * @package Envo\Model
 *
 * @property integer id
 * @property string  name
 * @property Module  module
 */
class Permission extends AbstractModel
{
	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $table = 'core_permissions';
	
	/**
	 * @var integer
	 */
	protected $id;
	
	/**
	 * @var string
	 */
	protected $name;
	
	/**
	 * @var Module
	 */
	protected $module;
	
	/**
	 * initialize the model
	 */
	public function initialize()
	{
		/* defines the relation to Module */
		$this->belongsTo('module_id', Module::class, 'id', [ 'alias' => 'module']);
	}
	
	/**
	 * @return int
	 */
	public function getId() : int
	{
		return $this->id;
	}
	
	/**
	 * @param int $id
	 */
	public function setId( int $id )
	{
		$this->id = $id;
	}
	
	/**
	 * @return string
	 */
	public function getName() : string
	{
		return $this->name;
	}
	
	/**
	 * @param string $name
	 */
	public function setName( string $name )
	{
		$this->name = $name;
	}
	
	/**
	 * @return Module
	 */
	public function getModule() : Module
	{
		return $this->module;
	}
	
	/**
	 * @param Module $module
	 */
	public function setModule( Module $module )
	{
		$this->module = $module;
	}
}