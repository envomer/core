<?php

namespace Envo;

use Envo\Model\Module;
use Envo\Model\Permission;
use Envo\Model\Repository\PermissionRepository;
use Envo\Model\Repository\RoleRepository;
use Envo\Model\Repository\RuleRepository;
use Envo\Model\Role;
use Envo\Model\Rule;
use Phalcon\Mvc\Model\Resultset\Simple;

/**
 * Class AbstractRole
 *
 * @package Envo\Model
 *
 * @property integer      id
 * @property AbstractRole parent
 */
abstract class AbstractRole extends AbstractModel
{
	/**
	 * @var array
	 */
	protected $permissions;
	
	/**
	 * @var Module[]
	 */
	protected $allowedModules;
	
	/**
	 * @var integer
	 */
	public $id;
	
	/**
	 * @var AbstractRole
	 */
	public $parent;
	
	/**
	 * @var Role
	 */
	private $role;
	
	/**
	 * initialize the model
	 *
	 * @return void
	 */
	public function initialize()
	{
		$this->keepSnapshots(true);
	}
	
	/**
	 * Will also save the Model into the role table
	 * Update path information
	 *
	 * @param bool $success
	 * @param bool $exists
	 *
	 * @return bool
	 */
	protected function _postSave( $success, $exists ) : bool
	{
		/** @var RoleRepository $repo */
		$repo = Role::repo();
		
		if (null !== $this->parent && ! ($this->parent instanceof Role) ){
			//var_dump($this->parent_id, $this->name);
			$this->parent = $this->parent->getRole();
		}
		
		if(! $exists && $success){
			if ($this instanceof Role){
				/* we are a role so we do not need to create a new one*/
				$role = $this;
				$repo->addRole($role, $role->parent);
			}else{
				/* create a new role if it is a new model */
				$role = new Role();
				$role->type   = str_replace('\\', '_' , static::class);
				$role->role_id = $this->id;
				$role->parent = $this->parent;
				
				/* check if model provides somehow a name */
				$vars = get_object_vars($this);
				
				if (isset($vars['name'])){
					$role->name = $vars['name'];
				}
				elseif (method_exists($this, 'getName')){
					$role->name = $this->getName();
				}
				
				$role->save();
			}
		}
		elseif ($exists && null !== $this->getRole()->getSnapshotData() && $this->getRole()->hasChanged('parent_id')) {
			$repo->moveRole($this->getRole()->parent->id, $this->getRole()->id);
		}
		
		return true;
	}
	
	/**
	 * Get all allowed Modules for this role
	 *
	 * @return Simple
	 */
	public function getAllowedModules() : Simple
	{
		$this->initPermission();
		
		if (null === $this->allowedModules){
			$this->allowedModules = Module::repo()->in('id', array_keys($this->permissions))->get();
		}
		
		return $this->allowedModules;
	}
	
	public function getAllPermissions()
	{
		$this->initPermission();
		
		//todo get all permission
	}
	
	/**
	 * Generates a permission string with following structure:
	 * moduleSlug:permissionNumber
	 * Each combination of that structure is separated by a semicolon (;).
	 * e.g core:4;billing:2
	 * The permission number is the sum of the power of the permission ids for the module.
	 *
	 * @return string
	 */
	public function getPermissionString() : string
	{
		$permissionString = [];
		
		/** @var Module[] $modules */
		$modules = $this->getAllowedModules();
		
		foreach ($modules as $module){
			$permissionNumber = $this->permissions[$module->id];
			$permissionString[] = ($module->slug ? : $module->name) . ':' . $permissionNumber;
		}
		
		return implode(';', $permissionString);
	}
	
	/**
	 * Check the permission for this role for given module
	 * This will store all the permission for this role on the first hit
	 *
	 * @param string $permissionName
	 * @param string $moduleName
	 *
	 * @return bool
	 */
	public function canI( string $permissionName, string $moduleName) : bool
	{
		//todo get permission and module from cache
		/** @var Permission $permission */
		$permission = Permission::repo()->where('name', $permissionName)->getOne();
		/** @var Module $module */
		$module = Module::repo()->where('name', $moduleName)->getOne();
		
		if (null === $permission || null === $module) {
			//todo maybe throw exception?
			return false;
		}
		
		$this->initPermission();
		
		if(! isset($this->permissions[$module->getId()])){
			return false;
		}
		
		$permissionBit = 2**$permission->getId();
		
		return ($this->permissions[$module->getId()] & $permissionBit) === $permissionBit;
	}
	
	/**
	 * @return Role
	 */
	public function getRole() : Role
	{
		if ($this instanceof Role){
			return $this;
		}
		
		if (null === $this->role){
			/** @var RoleRepository $repo */
			$repo = Role::repo();
			
			$this->role = $repo->getByAbstractRole($this);
		}
		
		return $this->role;
	}
	
	/**
	 * Removes given permission from role for given module
	 *
	 * @param string $permission
	 * @param string $moduleName
	 *
	 * @return void
	 */
	public function refuse(string $permission, string $moduleName)
	{
		//todo implement me
	}
	
	/**
	 * @param AbstractRole $role
	 *
	 * @return void
	 */
	public function addRole(AbstractRole $role)
	{
		/** @var RoleRepository $repo */
		$repo = Role::repo();
		
		//todo check if role is already assigned
		
		$repo->addRole($this->getRole(), $role->getRole());
	}
	
	/**
	 * Adds given permission to this role for given module
	 *
	 * @param string $permissionName
	 * @param string $moduleName
	 *
	 * @return void
	 */
	public function grant( string $permissionName, string $moduleName)
	{
		//todo get permission and module from cache
		/** @var Permission $permission */
		$permission = Permission::repo()->where('name', $permissionName)->getOne();
		/** @var Module $module */
		$module = Module::repo()->where('name', $moduleName)->getOne();
		
		if (null === $permission || null === $module){
			return;
		}
		
		/** @var $repo RuleRepository */
		$repo = Rule::repo();
		
		/* check if rule already exists */
		$rule = $repo->getByRoleAndPermissionAndModule($this->getRole(),$permission, $module);
		
		if (null !== $rule && false !== $rule){
			/* rule already exists */
			return;
		}
		
		$rule = new Rule();
		$rule->role = $this->getRole();
		$rule->permission = $permission;
		$rule->module = $module;
		$rule->save();
	}
	
	/**
	 * Initialize the permission for this role
	 *
	 * @return void
	 */
	protected function initPermission()
	{
		if (null === $this->permissions){
			/** @var PermissionRepository $permissionRepo */
			$permissionRepo = Permission::repo();
			$role = $this;
			if (! ($role instanceof Role)){
				$role = $this->getRole();
			}
			$permissions = $permissionRepo->getByRoleId($role->id);
			
			foreach ($permissions as $modulePermission){
				$this->permissions[$modulePermission['module']] = $modulePermission['permission'];
			}
		}
	}
}