<?php

namespace Envo\Model\Repository;

use Envo\AbstractRepository;
use Envo\Model\Module;
use Envo\Model\Permission;
use Envo\Model\Role;
use Envo\Model\Rule;

class RuleRepository extends AbstractRepository
{
	/**
	 * @param Role       $role
	 * @param Permission $permission
	 * @param Module     $module
	 *
	 * @return \Envo\AbstractModel|Rule|null
	 */
	public function getByRoleAndPermissionAndModule(Role $role, Permission $permission, Module $module)
	{
		$queryBuilder = parent::createBuilder();
		$queryBuilder
			->from(Rule::class)
			->where('role_id = :role:', ['role' => $role->getId()])
			->andWhere('permission_id = :permission:', ['permission' => $permission->getId()])
			->andWhere('module_id = :module:', ['module' => $module->getId()])
		;
		
		return $queryBuilder->getOne();
	}
}