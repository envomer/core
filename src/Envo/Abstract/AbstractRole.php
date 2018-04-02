<?php

namespace Envo;

use Envo\Model\Module;
use Envo\Model\Permission;
use Envo\Model\Repository\PermissionRepository;
use Envo\Model\Repository\RoleRepository;
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
	protected $id;
	
	/**
	 * @var AbstractRole
	 */
	protected $parent;
	
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
		$repo = self::repo();
		
		if(! $exists && $success){
			if ($this instanceof Role){
				/* we are a role so we do not need to create a new one*/
				$role = $this;
			}else{
				/* create a new role if it is a new model */
				$role = new Role();
				$role->type   = str_replace('\\', '_' , static::class);
				$role->id     = $this->id;
				$role->parent = $this->parent;
				
				/* check if model provides somehow a name */
				$vars = get_object_vars($this);
				
				if (isset($vars['name'])){
					$role->name = $vars['name'];
				}
				elseif (method_exists($this, 'getName')){
					$role->name = $this->getName();
				}
			}
			
			if (null !== $role->parent && ! ($role->parent instanceof Role) ){
				$role->parent = $this->getRole();
			}
			
			$role->save();
			
			$repo->addRole($role, $role->parent);
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
			$permissions = $permissionRepo->getByRoleId($this->getId());
			
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
	 * @return int
	 */
	public function getId() : int
	{
		return $this->id;
	}
	
	/**
	 * @param int $id
	 */
	public function setId( int $id )
	{
		$this->id = $id;
	}
	
	/**
	 * @return AbstractRole
	 */
	public function getParent() : AbstractRole
	{
		return $this->parent;
	}
	
	/**
	 * @param AbstractRole $parent
	 */
	public function setParent( AbstractRole $parent )
	{
		$this->parent = $parent;
	}
	
	/**
	 * Adds given permission to this role for given module
	 *
	 * @param string $permissionName
	 * @param string $moduleName
	 *
	 * @return void
	 */
	public function addPermission( string $permissionName, string $moduleName)
	{
		//todo get permission and module from cache
		/** @var Permission $permission */
		$permission = Permission::repo()->where('name', $permissionName)->getOne();
		/** @var Module $module */
		$module = Module::repo()->where('name', $moduleName)->getOne();
		
		$rule = new Rule();
		$rule->role = $this;
		$rule->permission = $permission;
		$rule->module = $module;
	}
}