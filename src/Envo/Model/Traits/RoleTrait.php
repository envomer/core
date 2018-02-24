<?php

namespace Envo\Model\Traits;

use Envo\Model\Module;
use Envo\Model\Permission;
use Envo\Model\Repository\PermissionRepository;
use Envo\Model\Role;
use Envo\Model\RoleRelation;

/**
 * Trait LegalEntityTrait
 * @package Envo\Model
 */
trait RoleTrait
{
	protected $permissions;
	
	/**
	 * @param bool $success
	 * @param bool $exists
	 *
	 * @return bool
	 */
	protected function _postSave( $success, $exists ) : bool
	{
		if(! $exists && $success){
			/* create a new role if it is a new model */
			$role = new Role();
			$role->name   = $this->getName();
			$role->type   = str_replace('\\', '_' , static::class);
			$role->id     = $this->id;
			$role->save();
		}
		
		return true;
	}
	
	/**
	 * Check the permission for this role for given module
	 * This will store the all the permission for this role on the first hit
	 *
	 * @param Permission $permission
	 * @param Module     $module
	 *
	 * @return bool
	 */
	public function canI( Permission $permission, Module $module) : bool
	{
		if (null === $this->permissions){
			/** @var PermissionRepository $permissionRepo */
			$permissionRepo = Permission::repo();
			$permissions = $permissionRepo->getByRoleId($this->getId());
			foreach ($permissions as $modulePermission){
				$this->permissions[$modulePermission['module']] = $modulePermission['permission'];
			}
		}
		
		$permissionBit = 2**$permission->getId();
		
		return ($this->permissions[$module->getId()] & $permissionBit) === $permissionBit;
	}
	
	/**
	 * @param Role $child
	 */
	public function addChild( Role $child)
	{
		$relation = new RoleRelation();
		$relation->child = $child;
		$relation->parent = $this;
		$relation->save();
	}
	
	/**
	 * @param Role $parent
	 */
	public function addParent( Role $parent)
	{
		$relation = new RoleRelation();
		$relation->child = $this;
		$relation->parent = $parent;
		$relation->save();
	}
	
	/**
	 * @param Role $child
	 */
	public function removeChild( Role $child)
	{
		//todo implement me
	}
	
	/**
	 * @param Role $parent
	 */
	public function removeParent( Role $parent)
	{
		//todo implement me
	}
	
	/**
	 * get a name representation of this entity
	 *
	 * @return string
	 */
	abstract public function getName() :string ;
}