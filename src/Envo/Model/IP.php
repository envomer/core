<?php

namespace Envo\Model;

use Envo\AbstractModel;

class IP extends AbstractModel
{
	protected $table = 'core_ips';
	
	public $ip;
	
	public $created_at;
	
	public $user_id;
	
	public $id;
}