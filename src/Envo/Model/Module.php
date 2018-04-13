<?php

namespace Envo\Model;

use Envo\AbstractModel;

/**
 * Class Module
 *
 * @package Envo\Model
 *
 * @property integer id
 * @property string  name
 * @property string  slug
 */
class Module extends AbstractModel
{
	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $table = 'core_modules';
	
	/**
	 * @var integer
	 */
	public $id;
	
	/**
	 * @var string
	 */
	public $slug;
	
	/**
	 * @var  string
	 */
	public $name;
}