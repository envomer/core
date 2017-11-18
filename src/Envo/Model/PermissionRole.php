<?php

namespace Envo\Model;

use Envo\AbstractModel;

/**
 * Class PermissionRole
 *
 * @package Envo\Model
 */
class PermissionRole extends AbstractModel
{
    protected $table = 'core_permission_roles';
	
	/**
	 * @var LegalEntity
	 */
	protected $legalEntity;
	
	/**
	 * @var PermissionRule
	 */
	protected $rule;
	
	/**
	 * initialize the model
	 */
	public function initialize()
	{
		$this->belongsTo('legal_entity_id', LegalEntity::class, 'id', ['alias' => 'legalEntity']);
		$this->belongsTo('permission_rule_id', PermissionRule::class, 'id', [ 'alias' => 'rule']);
	}
}