<?php

namespace Envo\Model;

use Envo\AbstractModel;

/**
 * @property string      name
 * @property integer     id
 * @property string      type
 * @property LegalEntity parent
 */
class LegalEntity extends AbstractModel
{
	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $table = 'core_legal_entities';
	
	/**
	 * @var integer
	 */
	protected $id;
	
	/**
	 * @var string
	 */
	protected $type;
	
	/**
	 * @var LegalEntity[]
	 */
	protected $parents;
	
	/**
	 * @var LegalEntity[]
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
			LegalEntities::class,
			'parent_id',
			'child_id',
			static::class,
			'id',
			['alias' => 'children']
		);
		
		/* defines the parents */
		$this->hasManyToMany(
			'id',
			LegalEntities::class,
			'child_id',
			'parent_id',
			static::class,
			'id',
			['alias' => 'parents']
		);
		
		$this->hasManyToMany(
			'id',
			PermissionRole::class,
			'legal_entity_id',
			'permission_rule_id',
			static::class,
			'id',
			['alias' => 'permissions']
		);
		
	}
}