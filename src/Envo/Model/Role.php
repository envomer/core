<?php

namespace Envo\Model;

use Envo\AbstractModel;

/**
 * @property integer id
 * @property string  type
 * @property Role[]  parents
 * @property Role[]  children
 * @property string  name
 */
class Role extends AbstractModel
{
	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $table = 'core_roles';
	
	/**
	 * @var integer
	 */
	protected $id;
	
	/**
	 * @var string
	 */
	protected $type;
	
	/**
	 * @var Role[]
	 */
	protected $parents;
	
	/**
	 * @var Role[]
	 */
	protected $children;
	
	/**
	 * @var string
	 */
	protected $name;
	
	/**
	 * initialize the model
	 */
	public function initialize()
	{
		/* defines the children */
		$this->hasManyToMany(
			'id',
			RoleRelation::class,
			'parent_id',
			'child_id',
			static::class,
			'id',
			['alias' => 'children']
		);
		
		/* defines the parents */
		$this->hasManyToMany(
			'id', RoleRelation::class,
			'child_id',
			'parent_id',
			static::class,
			'id',
			['alias' => 'parents']
		);
		
		$this->hasManyToMany(
			'id',
			Rule::class,
			'legal_entity_id',
			'permission_rule_id',
			static::class,
			'id',
			['alias' => 'permissions']
		);
		
	}
}