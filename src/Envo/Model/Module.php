<?php

namespace Envo\Model;

use Envo\AbstractModel;
use Envo\Model\Traits\SlugTrait;

/**
 * Class Module
 *
 * @package Envo\Model
 *
 * @property ModuleUnit[] units
 */
class Module extends AbstractModel
{
	use SlugTrait;
	
	/**
	 * @var integer
	 */
	protected $id;
	
	/**
	 * @var ModuleUnit[]
	 */
	protected $units;
	
	/**
	 * initialize the model
	 */
	public function initialize()
	{
		$this->hasMany('id', ModuleUnit::class, 'module_id',[ 'alias' => 'units']);
	}
}