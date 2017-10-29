<?php

namespace Envo\Event\Model;

use Envo\AbstractModel;

class EventType extends AbstractModel
{
	protected $table = 'core_event_types';
	
	public $id;
	
	public $class;
	
	public $created_at;
}