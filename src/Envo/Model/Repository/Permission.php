<?php

namespace Envo\Model\Repository;

use Envo\AbstractRepository;
use Envo\Model\Permission as Model;
use Envo\Model\PermissionRule;
use Envo\Model\RoleUser;
use Envo\Model\PermissionRole;

class Permission extends AbstractRepository
{
    public function getByUserId($userId)
    {
        //$builder = parent::getQueryBuilder(['p' => Model::class]);
        $builder = parent::createBuilder();

        $builder->join(PermissionRole::class, 'pr.permission_id = p.id', 'pr');
        $builder->join(PermissionRule::class, 'r.id = pr.role_id', 'r');
        $builder->join(RoleUser::class, 'ru.role_id = r.id', 'ru');

        $bind = ['user_id' => $userId];
        $builder->where('ru.user_id = :user_id: AND p.state = 1', $bind);

        return $builder->getQuery()->execute();
    }
}