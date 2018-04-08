<?php

namespace Envo\Model;

use Envo\AbstractRole;
use Phalcon\Mvc\Model\MetaDataInterface;

/**
 * Class Role
 *
 * @package Envo\Model
 *
 * @property integer roleId
 * @property string  type
 * @property string  name
 */
class Role extends AbstractRole
{
	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $table = 'core_roles';
	
	/**
	 * the foreign id of the given type
	 *
	 * @var integer
	 */
	protected $roleId;
	
	/**
	 * this will be the class name of the current instance of the role
	 *
	 * @var string
	 */
	protected $type;
	
	/**
	 * The name of the role (optional)
	 *
	 * @var string
	 */
	protected $name;
	
	/**
	 * initialize the model
	 */
	public function initialize()
	{
		parent::initialize();
		
		/* define the parent */
		$this->belongsTo( 'parent_id', self::class, 'id', ['alias' => 'parent'] );
		
		$this->hasManyToMany( 'id',
			Rule::class,
			'role_id',
			'permission_id',
			Permission::class,
			'id',
			['alias' => 'permissions']
		);
	}
	
	/**
	 * @param MetaDataInterface $metaData
	 * @param bool              $exists
	 * @param mixed             $identityField
	 *
	 * @return bool
	 */
	protected function _preSave( MetaDataInterface $metaData, $exists, $identityField ) : bool
	{
		//parent::_preSave($metaData, $exists, $identityField);
		
		if (! $exists && null === $this->type){
			$this->type = str_replace('\\', '_' , static::class);
		}
		
		return true;
	}
	
	/**
	 * @return string
	 */
	public function getType() : string
	{
		return $this->type;
	}
	
	/**
	 * @param string $type
	 */
	public function setType( string $type )
	{
		$this->type = $type;
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
	 * @return int
	 */
	public function getRoleId() : int
	{
		return $this->roleId;
	}
	
	/**
	 * @param int|null $roleId
	 */
	public function setRoleId( int $roleId = null)
	{
		$this->roleId = $roleId;
	}
}