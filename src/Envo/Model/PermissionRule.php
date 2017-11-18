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
 * @property ModuleUnit  unit
 */
class PermissionRule extends AbstractModel
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
	
	/**
	 * @var ModuleUnit
	 */
	protected $unit;
	
	/**
	 * initialize the model
	 */
	public function initialize()
	{
		$this->belongsTo('unit_id', ModuleUnit::class, 'id', [ 'alias' => 'unit']);
	}
}