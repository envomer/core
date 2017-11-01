<?php

namespace Envo\Database\Migration;

use Envo\AbstractModel;

class Model extends AbstractModel
{
	protected $table = 'migrations';
	
	/**
	 * @var string
	 */
	public $migration;
	
	/**
	 * @var int
	 */
	public $batch;
}