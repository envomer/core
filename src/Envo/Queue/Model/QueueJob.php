<?php 

namespace Envo\Queue\Model;

use Envo\AbstractModel;

class QueueJob extends AbstractModel
{
	const STATUS_FAILED = -1;
	const STATUS_OK = 1;
	const STATUS_QUEUED = null;

	protected $table = 'core_queue_jobs';

	protected $fillable = array(
		'done', 'failed', 'status'
	);

	public function setDone($flag)
	{
		$this->done = $flag;
	}

	public function setStatus($flag)
	{
		$this->status = $flag;
	}
}