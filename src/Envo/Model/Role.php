<?php

namespace Envo\Model;

use Envo\AbstractModel;

/**
 * Class Role
 *
 * @package Envo\Model
 *
 * @property integer     id
 * @property LegalEntity legalEntity
 * @property integer     permission
 */
class Role extends AbstractModel
{
	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $table = 'core_roles';
	
	/**
	 * @var integer
	 */
	protected $id;
	
	/**
	 * @var LegalEntity
	 */
	protected $legalEntity;
	
	/**
	 * @var integer
	 */
	protected $permission;
	
	//todo create unit and module models
	protected $unit;
}