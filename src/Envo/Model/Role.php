<?php

namespace Envo\Model;

use Envo\AbstractRole;
use Phalcon\Mvc\Model\MetaDataInterface;

/**
 * Class Role
 *
 * @package Envo\Model
 *
 * @property integer role_id
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
	public $role_id;
	
	/**
	 * this will be the class name of the current instance of the role
	 *
	 * @var string
	 */
	public $type;
	
	/**
	 * The name of the role (optional)
	 *
	 * @var string
	 */
	public $name;
	
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
}