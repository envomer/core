<?php

namespace Envo\Model\Repository;

use Envo\AbstractRepository;
use Phalcon\Db;

/**
 * Class PermissionRepository
 *
 * @package Envo\Model\Repository
 */
class PermissionRepository extends AbstractRepository
{
	/**
	 * Will return for each module unit the corresponding permission number for the given role
	 *
	 * @param integer $roleId
	 *
	 * @return array an associated array with the keys 'module' and 'permission'
	 */
	public function getByRoleId( $roleId)
    {
        $select = 'SELECT cru.module_id AS module ,BIT_OR(pow(2,cru.permission_id)) AS permission
				   FROM `core_roles_relation` cr
				   LEFT JOIN core_rules cru ON cr.`child_id` = cru.`role_id` OR cr.parent_id = cru.role_id
				   WHERE cr.child_id = ?
				   GROUP BY cru.module_id';
        
        $result = parent::query($select, [0 => $roleId]);
        $result->setFetchMode(Db::FETCH_ASSOC);
        
        return $result->fetch();
    }
}