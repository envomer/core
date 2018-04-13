<?php

namespace Envo;

use Envo\Model\Module;
use Envo\Model\Permission;
use Envo\Model\Repository\PermissionRepository;
use Envo\Model\Repository\RoleRepository;
use Envo\Model\Repository\RuleRepository;
use Envo\Model\Role;
use Envo\Model\Rule;

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
}