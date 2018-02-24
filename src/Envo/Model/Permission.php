<?php

namespace Envo\Model;

use Envo\AbstractModel;

/**
 * Class Role
 *
 * @package Envo\Model
 *
 * @property integer    id
 * @property Role       rule
 * @property integer    permission
 * @property Module unit
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
	 * @var Rule[]
	 */
	protected $rules;
	
	/**
	 * @var Module
	 */
	protected $module;
	
	/**
	 * initialize the model
	 */
	public function initialize()
	{
		/* defines the relation to ModuleUnit */
		$this->belongsTo('unit_id', Module::class, 'id', [ 'alias' => 'module']);
		
		/* defines the rule */
		$this->hasManyToMany(
			'id', Rule::class,
			'role_id',
			'permission_id',
			static::class,
			'id',
			['alias' => 'rules']
		);
	}
}