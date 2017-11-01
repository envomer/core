<?php

namespace Envo\Model;

use Envo\AbstractModel;

/**
 * Class IP
 *
 * @package Envo\Model
 */
class IP extends AbstractModel
{
	/**
	 * @var string
	 */
	protected $table = 'core_ips';
	
	/**
	 * @var string
	 */
	public $ip;
	
	/**
	 * @var string
	 */
	public $created_at;
	
	/**
	 * @var int
	 */
	public $user_id;
	
	/**
	 * @var int
	 */
	public $id;
}