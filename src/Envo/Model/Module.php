<?php

namespace Envo\Model;

use Envo\AbstractModel;
use Envo\Model\Traits\SlugTrait;

/**
 * Class Module
 *
 * @package Envo\Model
 *
 * @property Unit[] units
 */
class Module extends AbstractModel
{
	use SlugTrait;
	
	/**
	 * @var integer
	 */
	protected $id;
	
	/**
	 * @var Unit[]
	 */
	protected $units;
	
	/**
	 * initialize the model
	 */
	public function initialize()
	{
		$this->hasMany('id', Unit::class, 'module_id',['alias' => 'units']);
	}
}