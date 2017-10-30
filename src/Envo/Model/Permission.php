<?php

namespace Envo\Model;

use Envo\AbstractModel;

/**
 * Class Permission
 *
 * @package Envo\Model
 *
 * @property integer     id
 * @property LegalEntity legalEntity
 * @property Role 		 role
 */
class Permission extends AbstractModel
{
	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $table = 'core_permissions';
	
	/**
	 * @var integer
	 */
	protected $id;
	
	/**
	 * @var LegalEntity
	 */
	protected $legalEntity;
	
	/**
	 * @var Role
	 */
	protected $role;
	
	/**
	 * initialize the model
	 */
	public function initialize()
	{
		$this->belongsTo('legal_entity_id', LegalEntity::class, 'id', ['alias' => 'legalEntity']);
		$this->belongsTo('role_id', Role::class, 'id', ['alias' => 'role']);
	}
}