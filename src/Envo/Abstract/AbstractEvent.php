<?php

namespace Envo;

use Envo\Notification\Notification;

use Envo\Support\IP;
use Envo\Support\File;
use Envo\Support\Date;
use Envo\AbstractModel;

use Envo\Event\Model\Event;
use Envo\Event\Model\EventType;
use Envo\Event\Model\EventModel;

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
		if( ! config('app.events.enabled', false) ) {
			return $this;
		}

		// in case an event is given as first parameter()
		// then just set the event instance without creating
		// a new event instance
		if( is_a($message, AbstractEvent::class) ) {
			return $this->event = $message;
		}

		if( is_a($message, AbstractModel::class) ) {
			$model = $message;
			$message = null;
		}

		$event = new Event;
		$user = ! defined('APP_CLI') ? user() : null;
		if( $user && $user->loggedIn ) {
			$event->user_id = $user->id;
			if( isset($user->client_id) ) {
				$event->client_id = $user->client_id;
			}
		}

		$event->created_at = date('Y-m-d H:i:s');
		$ip = resolve(IP::class)->getIpAddress();

		// if( $ip ) {
		// 	$userip = CoreIp::getByIp($ip);
		// 	if( ! $userip ) {
		// 		$userip = new UserIp;
		// 		$userip->ip = $ip;
		// 		$userip->created_at = date('Y-m-d H:i:s');
		// 		$userip->user_id = $user ? $user->id : null;
		// 		$userip->save();
		// 	}
		// 	$userip = $userip->id;
		// 	$event->ip_id = $userip;
		// }

		$this->setMessage($event, $message);

		$this->setEventType($event);
		$this->setModel($event, $model);
		$this->setData($event, $data);

		$this->event = $event;

		if( $save ) {
			$event->save();
		}

		$filepath = APP_PATH . 'storage/framework/logs/events/events-' . date('Y-m-d').'.log';
		File::append($filepath, "\n\r" . $event->toReadableString(get_called_class()));

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

	/**
	 * Set message
	 *
	 * @param AbstractEvent $event
	 * @param string $message
	 * @return AbstractEvent
	 */
	public function setMessage($event, $message)
	{
		if( ! $message ) {
			return false;
		}

		if( is_array($message) || is_object($message) ) {
			$message = json_encode($message);
		}

		$event->message = $message;

		return $event;
	}

	/**
	 * Set data
	 *
	 * @param AbstractEvent $event
	 * @param string $data
	 * @return AbstractEvent
	 */
	public function setData($event, $data)
	{
		if( ! $data ) {
			return false;
		}

		if( is_array($data) || is_object($data) ) {
			$data = json_encode($data);
		}

		$event->data = $data;

		return $event;
	}

	/**
	 * Set model
	 *
	 * @param AbstractEvent $event
	 * @param AbstractModel $model
	 * @return AbstractEvent
	 */
	public function setModel($event, $model)
	{
		if( ! $model ) {
			return false;
		}

		$modelClass = get_class($model);

		$eventModel = EventModel::findFirst(['class=?0', 'bind' => [$modelClass]]);

		if( ! $eventModel ) {
			$eventModel = new EventModel();
			$eventModel->class = $modelClass;
			$eventModel->created_at = Date::now();
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

		$eventType = EventType::findFirst(['class=?0', 'bind' => [$class]]);

		if( ! $eventType ) {
			$eventType = new EventType();
			$eventType->class = $class;
			$eventType->created_at = Date::now();
			$eventType->save();
		}

		$event->event_type_id = $eventType->id;

		return $event;
	}

	/**
	 * Set event
	 *
	 * @param AbstractEvent $event
	 * @return self
	 */
	public function setEvent($event)
	{
		$this->event = $event;
		return $this;
	}

	/**
	 * Get description
	 *
	 * @return string|null
	 */
	public function getDescription()
	{
		return null;
	}

	/**
	 * Get event
	 *
	 * @return AbstractEvent
	 */
	public function getEvent()
	{
		return $this->event;
	}

	/**
	 * Notify users
	 *
	 * @param AbstractUser[] $users
	 * @param string $data
	 * @return bool
	 */
	public function notify($users = null, $data = null)
	{
		$notification = new Notification();
		return $notification->send($this, $users, $data);
	}

	/**
	 * Get event name
	 *
	 * @return string
	 */
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

	public function save()
	{
		return $this->event ? $this->event->save() : null;
	}
}