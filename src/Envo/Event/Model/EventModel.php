<?php 

namespace Envo\Event\Model;

use Envo\AbstractModel;

class EventModel extends AbstractModel
{
	protected $table = 'core_event_models';
	
	public $class;
	
	public $created_at;
	
	public $id;
}