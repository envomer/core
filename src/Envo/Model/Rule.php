<?php

namespace Envo\Model;

use Envo\AbstractModel;

/**
 * Class PermissionRole
 *
 * @package Envo\Model
 */
class Rule extends AbstractModel
{
    protected $table = 'core_rules';
	
	/**
	 * @var Role
	 */
	protected $role;
	
	/**
	 * @var Permission
	 */
	protected $permission;
	
	/**
	 * initialize the model
	 */
	public function initialize()
	{
		$this->belongsTo('role_id', Role::class, 'id', [ 'alias' => 'role']);
		$this->belongsTo('permission_id', Permission::class, 'id', [ 'alias' => 'permission']);
	}
}