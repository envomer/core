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
}