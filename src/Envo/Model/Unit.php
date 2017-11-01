<?php

namespace Envo\Model;

use Envo\AbstractModel;
use Envo\Model\Traits\SlugTrait;

/**
 * Class Unit
 *
 * @package Envo\Model
 */
class Unit extends AbstractModel
{
	use SlugTrait;
	
	/**
	 * @var integer
	 */
	protected $id;
	
	/**
	 * @var Module
	 */
	protected $module;
	
	/**
	 * initialize the model
	 */
	public function initialize()
	{
		$this->belongsTo('module_id', Module::class, 'id',['alias' => 'module']);
	}
}