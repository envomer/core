<?php

namespace Envo\Model;

use Envo\AbstractModel;

/**
 * Class LegalEntities
 *
 * @package Envo\Model
 *
 * @property Role child
 * @property Role parent
 */
class RoleRelation extends AbstractModel
{
	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $table = 'core_roles_self';
	
	/**
	 * @var Role $parent
	 */
	protected $parent;
	
	/**
	 * @var Role $child
	 */
	protected $child;
	
	/**
	 * initialize the model
	 */
	public function initialize()
	{
		$this->belongsTo('parent_id', Role::class, 'id', [ 'alias' => 'parent']);
		$this->belongsTo('child_id', Role::class, 'id', [ 'alias' => 'child']);
	}
}