<?php

namespace Envo\Event\Model;

use Envo\AbstractEvent;
use Envo\AbstractModel;

use Envo\Support\IP;

class Event extends AbstractModel
{
	protected $table = 'core_events';

	public function initialize()
	{
		$this->hasOne('model_id', '\Core\Model\EventModel', 'id', ['alias' => 'model']);
		$this->hasOne('event_type_id', '\Core\Model\EventType', 'id', ['alias' => 'type']);
		$this->hasOne('user_id', '\Core\Model\User', 'id', ['alias' => 'user']);
		$this->hasOne('ip_id', '\Core\Model\UserIp', 'id', ['alias' => 'ip']);
	}

	public function index($attributes, $user)
	{
		$where = 'team_id = :team_id:';
		$bind = array('team_id' => $user->team_id);
		
		if( $user->isAdmin() ) {
			$where = '';
			$bind = array();
		}

		return [
			// 'conditions' => $where,
			// 'bind' => $bind,
			// 
			'with' => array(
				'type',
				'user',
				'ip',
				'model'
			),
			'order' => 'id DESC',
			'equals' => array('user_id', 'team_id'),
			'like' => array('message', 'data', 'id')
		];
	}

	public function getEventType()
	{
		$eventTypes = \Core\Service\EventTypeService::getAllEventTypes();
		return $eventTypes[$this->event_type_id];
	}

	public function getEventClass()
	{
		$class = $this->getEventType()->class;
		$class = $class::getInstance();
		$class->setEvent($this);
		return $class;
	}

	public function getDescription()
	{
		return $this->getEventClass()->getDescription();
	}

	public function getModelObject()
	{
		if( ! $this->model_id ) return null;
		$model = $this->model;
		$class = $model->class;
		return $class::findFirstById($this->model_entry_id);
	}

	public function toReadableString($model)
	{
		$username = ($user = user()) ? $user->username : null;
		$username .= ($username ? ' ' : '') . resolve(IP::class)->getIpAddress();
		$message = isset($this->message) ? $this->message : (isset($this->data) ? $this->data : '');
		return $this->created_at . '| ' . str_replace('\\Events\\', '.', $model) . ' | ' .$username .' | ' . $message;
	}

	public function getCreatedAt()
	{
		return $this->created_at;
	}
}