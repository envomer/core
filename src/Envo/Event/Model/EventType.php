<?php

namespace Envo\Event\Model;

use Envo\AbstractModel;

class EventType extends AbstractModel
{
	const VISIBILITY_SHOW = 1;
	const VISIBILITY_HIDE = 2;
	
	protected $table = 'core_event_types';
	
	/**
	 * @var int
	 */
	public $id;
	
	/**
	 * @var string
	 */
	public $class;
	
	/**
	 * @var int
	 */
	public $visibility;
	
	/**
	 * @var string
	 */
	public $created_at;
}