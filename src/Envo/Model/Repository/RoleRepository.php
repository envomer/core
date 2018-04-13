<?php

namespace Envo\Model\Repository;

use Envo\AbstractModel;
use Envo\AbstractRepository;
use Envo\AbstractRole;
use Envo\Model\Role;
use Phalcon\Db;

/**
 * Class RoleRepository
 *
 * @package Envo\Model\Repository
 */
class RoleRepository extends AbstractRepository
{
	
	/**
	 * @param Role      $newRole
	 * @param Role|null $parentRole
	 *
	 * @return mixed
	 */
	public function addRole( $newRole, $parentRole = null)
	{
		$select = '
			INSERT INTO `core_roles_paths` (`parent_id`,`child_id`,`path_length`)
			SELECT * FROM(
			SELECT `parent_id` parent_id, ? child_id,(`path_length` + 1) path_length
			FROM`core_roles_paths`
			WHERE `child_id` = ?
			UNION
			ALL
			SELECT ?, ?,0
			) temp
			WHERE (`child_id`, parent_id, path_length) NOT IN (
				SELECT child_id, parent_id, path_length FROM `core_roles_paths` crp
			);
		';
		
		$id = $newRole->id;
		
		$params = [$id, null !== $parentRole ? $parentRole->id : null, $id, $id];
		
		$result = parent::query($select, $params);
		$result->setFetchMode(Db::FETCH_ASSOC);
		
		return $result->fetch();
	}
	
	/**
	 * @param int $parentId
	 *
	 * @return mixed
	 */
	public function deletePaths( int $parentId)
	{
		$sql = '
		DELETE a
  		FROM `core_roles_paths` AS a
    	JOIN `core_roles_paths` AS d ON a.`child_id` = d.`child_id`
    	LEFT JOIN `core_roles_paths` AS x ON x.`parent_id` = d.`parent_id` AND x.`child_id` = a.`parent_id`
  		WHERE d.`parent_id` = ?
    	AND x.`parent_id` IS NULL ;
		';
		
		$params = [$parentId];
		
		$result = parent::query($sql, $params);
		$result->setFetchMode(Db::FETCH_ASSOC);
		
		return $result->fetch();
	}
	
	/**
	 * @param int $newParentId
	 * @param int $oldParentId
	 *
	 * @return mixed
	 */
	public function moveRole( int $newParentId, int $oldParentId)
	{
		$this->deletePaths($oldParentId);
		
		$sql = '
		INSERT INTO core_roles_paths (parent_id, child_id, path_length)
		SELECT supertree.parent_id, subtree.child_id, supertree.path_length+subtree.path_length+1
		FROM core_roles_paths AS supertree
		JOIN core_roles_paths AS subtree
		WHERE subtree.parent_id = ?
		AND supertree.child_id = ?;
		';
		
		$params = [$oldParentId, $newParentId];
		
		$result = parent::query($sql, $params);
		$result->setFetchMode(Db::FETCH_ASSOC);
		
		return $result->fetch();
	}
	
	/**
	 * @param AbstractRole $role
	 *
	 * @return Role|AbstractModel
	 */
	public function getByAbstractRole( AbstractRole $role) : Role
	{
		$type = str_replace('\\', '_', \get_class($role));
		
		$queryBuilder = parent::createBuilder();
		$queryBuilder
			->from(Role::class)
			->where('role_id = :roleId:', ['roleId' => $role->getId()])
			->andWhere('type = :type:', ['type' => $type])
		;
		
		return $queryBuilder->getOne();
	}
}