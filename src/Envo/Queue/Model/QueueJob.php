<?php 

namespace Envo\Queue\Model;

use Envo\AbstractModel;

class QueueJob extends AbstractModel
{
	public const STATUS_FAILED = -1;
	public const STATUS_OK = 1;
	public const STATUS_QUEUED = null;
	
	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $table = 'core_queue_jobs';
	
	/**
	 * @var int
	 */
	public $id;
	
	/**
	 * @var string
	 */
	public $name;
	
	/**
	 * @var string
	 */
	public $queue;
	
	/**
	 * @var int
	 */
	public $type_id;
	
	/**
	 * @var int
	 */
	public $status;
	
	/**
	 * @var int
	 */
	public $done;
	
	/**
	 * @var int
	 */
	public $attempts;
	
	/**
	 * @var string
	 */
	public $payload;
	
	/**
	 * @var string
	 */
	public $exception;
	
	/**
	 * @var string
	 */
	public $reserved_at;
	
	/**
	 * @var string
	 */
	public $available_at;
	
	/**
	 * @var string
	 */
	public $created_at;
	
	/**
	 * @var string
	 */
	public $failed_at;
	
}