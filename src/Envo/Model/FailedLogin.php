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
class FailedLogin extends AbstractModel
{
	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $table = 'core_failed_logins';
	
	/**
	 * @var integer
	 */
	public $user_id;
	
	/**
	 * @var string
	 */
	public $ip;
	
	/**
	 * @var Module
	 */
	// public $attempted;

	public $created_at;
	
	public $updated_at;
}