<?php

namespace Envo\Model;

use Envo\AbstractModel;

/**
 * Class Role
 *
 * @package Envo\Model
 *
 * @property integer id
 * @property string  name
 * @property Module  module
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
	public $id;
	
	/**
	 * @var string
	 */
	public $name;
	
	/**
	 * @var Module
	 */
	public $module;
	
	/**
	 * initialize the model
	 */
	public function initialize()
	{
		/* defines the relation to Module */
		$this->belongsTo('module_id', Module::class, 'id', [ 'alias' => 'module']);
	}
}