<?php 

namespace Envo\Queue\Model;

use Envo\AbstractModel;

class QueueJobType extends AbstractModel
{
	protected $table = 'core_queue_job_types';
	
	public const STATUS_ENABLED = 1;
	public const STATUS_DISABLED = 0;
	
	/**
	 * @var int
	 */
	public $id;
	
	/**
	 * @var string
	 */
	public $class_namespace;
	
	/**
	 * @var int
	 */
	public $status;
	
	/**
	 * @var string
	 */
	public $created_at;
}