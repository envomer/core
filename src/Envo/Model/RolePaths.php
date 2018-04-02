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
	protected $parent;
	
	/**
	 * @var Role $child
	 */
	protected $child;
	
	/**
	 * @var integer $pathLength
	 */
	protected $pathLength;
	
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
	
	/**
	 * @return Role
	 */
	public function getChild() : Role
	{
		return $this->child;
	}
	
	/**
	 * @param Role $child
	 */
	public function setChild( Role $child )
	{
		$this->child = $child;
	}
	
	/**
	 * @return Role
	 */
	public function getParent() : Role
	{
		return $this->parent;
	}
	
	/**
	 * @param Role $parent
	 */
	public function setParent( Role $parent )
	{
		$this->parent = $parent;
	}
	
	/**
	 * @return int
	 */
	public function getPathLength() : int
	{
		return $this->pathLength;
	}
	
	/**
	 * @param int $pathLength
	 */
	public function setPathLength( int $pathLength )
	{
		$this->pathLength = $pathLength;
	}
}