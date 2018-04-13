<?php

namespace Envo\Model;

use Envo\AbstractModel;

/**
 * Class LegalEntities
 *
 * @package Envo\Model
 *
 * @property Role    child
 * @property Role    parent
 * @property integer pathLength
 */
class RolePaths extends AbstractModel
{
	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $table = 'core_roles_paths';
	
	/**
	 * @var Role $parent
	 */
	public $parent;
	
	/**
	 * @var Role $child
	 */
	public $child;
	
	/**
	 * @var integer $pathLength
	 */
	public $pathLength;
	
	/**
	 * initialize the model
	 *
	 * @return void
	 */
	public function initialize()
	{
		$this->belongsTo('parent_id', Role::class, 'id', [ 'alias' => 'parent']);
		$this->belongsTo('child_id', Role::class, 'id', [ 'alias' => 'child']);
	}
}