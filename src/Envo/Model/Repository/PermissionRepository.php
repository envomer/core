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
	public function getByRoleId( $roleId ) : array
	{
        $select = '
			SELECT module_id as module, BIT_OR(pow(2,cr.permission_id)) as permission
			FROM core_rules cr
			WHERE cr.role_id IN
			(
				SELECT crumbs.`parent_id`
 				FROM `core_roles` AS node
    			JOIN `core_roles_paths` AS path ON node.`id` = path.`child_id`
    			JOIN `core_roles_paths` AS crumbs ON crumbs.`child_id` = path.`child_id`
  				WHERE path.`child_id` = ?
 			)
 			GROUP BY cr.module_id;
		';
        
        $result = parent::query($select, [0 => $roleId]);
        $result->setFetchMode(Db::FETCH_ASSOC);
        
        return $result->fetchAll();
    }
}