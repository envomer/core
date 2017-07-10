<?php

namespace Envo\Foundation;

use Envo\Notification\Notification;

class AbstractEvent
{
	const NOTIFY_VIA_NOTIFICATION = 'notification';
	const NOTIFY_VIA_EMAIL = 'email';
	const NOTIFY_VIA_SMS = 'sms';
	const NOTIFY_VIA_PUSHOVER = 'pushover';

	protected static $instance = null;
	protected $event = null;

	public function __construct($message = null, $save = true, $model = null, $data = null)
	{
		// in case an event is given as first pg_parameter_status()
		// then just set the event instance without creating
		// a new event instance
		if( is_a($message, 'AbstractModel') ) {
			return $this->event = $message;
		}

		$event = new \Core\Model\Event;
		$user = ! defined('APP_CLI') ? \Auth::user() : null;
		if( $user && $user->loggedIn ) {
			$event->user_id = $user->id;
			if( isset($user->client_id) )
				$event->client_id = $user->client_id;
		}
		
		if( ! defined('APP_CLI') && ($loginDomain = \LoginDomain::get(true)) ) {
			$event->login_domain_id = $loginDomain->id;
		}

		$event->created_at = date('Y-m-d H:i:s');
		$ip = \IP::getIpAddress();

		if( $ip ) {
			$userip = \Core\Model\UserIpRepository::getByIp($ip);
			if( ! $userip ) {
				$userip = new \Core\Model\UserIp;
				$userip->ip = $ip;
				$userip->created_at = date('Y-m-d H:i:s');
				$userip->user_id = $user ? $user->id : null;
				$userip->save();
				// \Cache::delete('userip.ip.' . md5($ip));
			}
			$userip = $userip->id;
			$event->ip_id = $userip;
		}

		$this->setMessage($event, $message);

		$this->setEventType($event);
		$this->setModel($event, $model);
		$this->setData($event, $data);

		$this->event = $event;

		if( $save ) {
			$event->save();
		}

		$filepath = APP_PATH . 'storage/logs/events-' . date('Y-m-d').'.log';
		\File::append($filepath, "\n\r" . $event->toReadableString(get_called_class()));

		return $event;
	}

	/**
	 * Get instance
	 */
	public static function getInstance()
	{
		if(! self::$instance) {
			$class = get_called_class();
			$instance = new $class(null, false);
		}
		return $instance;
	}

	public function setMessage($event, $message)
	{
		if( ! $message ) return false;
		if( is_array($message) || is_object($message) ) $message = json_encode($message);

		$event->message = $message;

		return $event;
	}

	public function setData($event, $data)
	{
		if( ! $data ) return false;
		if( is_array($data) || is_object($data) ) {
			$data = json_encode($data);
		}

		$event->data = $data;

		return $event;
	}

	public function setModel($event, $model)
	{
		if( ! $model ) return false;

		$modelClass = get_class($model);

		$eventModel = \Core\Model\EventModel::findFirst(['class=?0', 'bind' => [$modelClass]]);

		if( ! $eventModel ) {
			$eventModel = new \Core\Model\EventModel();
			$eventModel->class = $modelClass;
			$eventModel->save();
		}

		$event->model_id = $eventModel->id;

		if( isset($model->id) ) {
			$event->model_entry_id = $model->id;
		}

		return $event;
	}

	/**
	 * Set the current called class (event type)
	 */
	public function setEventType($event)
	{
		$class = get_called_class();

		$eventType = \Core\Model\EventType::findFirst(['class=?0', 'bind' => [$class]]);

		if( ! $eventType ) {
			$eventType = new \Core\Model\EventType();
			$eventType->class = $class;
			$eventType->save();
		}

		$event->event_type_id = $eventType->id;

		return $event;
	}

	public function setEvent($event)
	{
		$this->event = $event;
		return $this;
	}

	public function getDescription()
	{
		return null;
	}

	public function getEvent()
	{
		return $this->event;
	}

	public function notify($users = null, $data = null)
	{
		$notification = new Notification();
		return $notification->send($this, $users, $data);
	}

	public function getName()
	{
		return get_called_class();
	}

	public function getMessage()
	{
		return $this->event->getMessage();
	}

	public function userFriendly()
	{
		return [
			'title' => \_t('events.' . lcfirst( str_replace('\Events\\', '', $this->getName()))),
			'description' => $this->getDescription(),
			'id' => $this->event->getId(),
			'created_at' => $this->event->getCreatedAt()
		];
	}

	public function via()
	{
		return null;
	}	
}