<?php

namespace Envo\Model\Repository;

use Envo\Model\Permission;
use Envo\Model\Role;
use Envo\Model\RoleUser;
use Envo\Model\PermissionRole;

class PermissionRepository extends \Envo\AbstractRepository
{
    public function getByUserId($userId)
    {
        $builder = parent::getQueryBuilder(['p' => Permission::class]);

        $builder->join(PermissionRole::class, 'pr.permission_id = p.id', 'pr');
        $builder->join(Role::class, 'r.id = pr.role_id', 'r');
        $builder->join(RoleUser::class, 'ru.role_id = r.id', 'ru');

        $bind = ['user_id' => $userId];
        $builder->where('ru.user_id = :user_id: AND p.state = 1', $bind);

        return $builder->getQuery()->execute();
    }
}