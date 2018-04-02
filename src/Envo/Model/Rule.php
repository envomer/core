<?php

namespace Envo\Model;

use Envo\AbstractModel;

/**
 * Class PermissionRole
 *
 * @package Envo\Model
 *
 * @property Role       role
 * @property Permission permission
 * @property Module     module
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
	 * @var Module
	 */
	protected $module;
	
	/**
	 * initialize the model
	 */
	public function initialize()
	{
		$this->belongsTo('role_id', Role::class, 'id', [ 'alias' => 'role']);
		$this->belongsTo('permission_id', Permission::class, 'id', [ 'alias' => 'permission']);
		$this->belongsTo('module_id', Module::class, 'id', ['alias' => 'module']);
	}
}