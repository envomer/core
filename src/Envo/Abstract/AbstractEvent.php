<?php

namespace Envo;

use Envo\Model\User;
use Envo\Support\IP;
use Envo\Support\File;
use Envo\Support\Date;
use Envo\Model\IP as IPModel;
use Envo\Event\Model\Event;
use Envo\Event\Model\EventType;
use Envo\Notification\Notification;

class AbstractEvent
{
	const NOTIFY_VIA_NOTIFICATION = 'notification';
	const NOTIFY_VIA_EMAIL = 'email';
	const NOTIFY_VIA_SMS = 'sms';
	const NOTIFY_VIA_PUSHOVER = 'pushover';

	protected static $instance;
	protected $event;
	
	/**
	 * AbstractEvent constructor.
	 *
	 * @param null $message
	 * @param bool $save
	 * @param null $model
	 * @param null $data
	 *
	 */
	public function __construct($message = null, $save = true, $model = null, $data = null)
	{
		if( ! config('app.events.enabled', false) ) {
			return;
		}

		// in case an event is given as first parameter()
		// then just set the event instance without creating
		// a new event instance
		if( is_a($message, self::class) ) {
			return $this->event = $message;
		}

		if( is_a($message, AbstractModel::class) ) {
			$model = $message;
			$message = null;
		}
		
		$eventClass = config('app.classmap.event', Event::class);
		$event = new $eventClass;
		$user = ! defined('APP_CLI') ? user() : null;
		if( $user && $user->loggedIn ) {
			$event->user_id = $user->id;
			if( isset($user->team_id) ) {
				$event->team_id = $user->team_id;
			}
		}

		$event->created_at = date('Y-m-d H:i:s');
		$ip = resolve(IP::class)->getIpAddress();

		 if( $ip ) {
		 	$ipModel = config('app.classmap.ip', IPModel::class);
		 	$userIP = $ipModel::repo()->where('ip', $ip)->getOne();
		 	if( ! $userIP ) {
		 		$userIP = new $ipModel();
		 		$userIP->ip = $ip;
		 		$userIP->created_at = Date::now();
		 		$userIP->user_id = $user ? $user->id : null;
		 		$userIP->save();
		 	}
		 	$event->ip_id = $userIP->id;
		 }

		$this->setMessage($event, $message);

		$this->setEventType($event);
		$this->setModel($event, $model);
		$this->setData($event, $data);

		$this->event = $event;

		if( $save ) {
			$event->save();
		}

		$filePath = APP_PATH . 'storage/logs/events/events-' . date('Y-m-d').'.log';
		File::append($filePath, "\n\r" . $event->toReadableString(static::class));

		return $event;
	}

	/**
	 * Get instance
	 */
	public static function getInstance()
	{
		if(! self::$instance) {
			$class = static::class;
			self::$instance = new $class(null, false);
		}

		return self::$instance;
	}

	/**
	 * Set message
	 *
	 * @param Event $event
	 * @param mixed $message
	 *
	 * @return Event|bool
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
	 *
	 * @return bool|AbstractEvent
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
	 * @param Event $event
	 * @param AbstractModel $model
	 * @return Event|bool
	 */
	public function setModel($event, $model)
	{
		if( ! $model ) {
			return false;
		}

		$modelClass = get_class($model);

		$eventType = config('app.classmap.event_type', EventType::class);
		$eventModel = $eventType::findFirst(['class=?0', 'bind' => [$modelClass]]);

		if( ! $eventModel ) {
			$eventModel = new $eventType();
			$eventModel->class = $modelClass;
			$eventModel->created_at = Date::now();
			$eventModel->save();
		}

		$event->model_id = $eventModel->id;

		if( $model && isset($model->id) ) {
			$event->model_entry_id = $model->id;
		}

		return $event;
	}
	
	/**
	 * Set the current called class (event type)
	 *
	 * @param $event
	 *
	 * @return mixed
	 */
	public function setEventType($event)
	{
		$class = static::class;
		
		$eventType = config('app.classmap.event_type', EventType::class);
		$eventTypeResult = $eventType::findFirst(['class=?0', 'bind' => [$class]]);

		if( ! $eventTypeResult ) {
			$eventTypeResult = new $eventType();
			$eventTypeResult->class = $class;
			$eventTypeResult->created_at = Date::now();
			$eventTypeResult->save();
		}

		$event->event_type_id = $eventTypeResult->id;

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
	 * @return Event
	 */
	public function getEvent()
	{
		return $this->event;
	}

	/**
	 * Notify users
	 *
	 * @param User[] $users
	 * @param string $data
	 * @return bool
	 */
	public function notify($users = null, $data = null)
	{
		// TODO implement
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
		return static::class;
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