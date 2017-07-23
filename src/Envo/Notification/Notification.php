<?php

namespace Envo\Notification;

use Envo\AbstractEvent;

class Notification
{
    public $pusher = null;
    public $redis = null;
    
    public function send(AbstractEvent $event, $users = null, $data  = null)
    {
        if( ! $users ) {
            $users = $event->to();
        }

        if( ! $users ) {
            return false;
        }

		return $this->sendToUsers($event, $users);
    }

    public function sendToUsers($event, $to)
    {
		$notificationType = env('NOTIFICATION');
		
		if( ! $notificationType ) {
			return false;
		}

		if( $notificationType == 'pusher' ) {
            // return $this->pusherSend($user, $to);
		}
		else if( $notificationType == 'socketio' && env('REDIS_HOST') && env('REDIS_PORT') && env('REDIS_ACTIVE') ) {
			return $this->redisSend($event, $to);
		}

		return false;
    }

    public function redisSend($event, $to)
    {
        if( $this->redis === false ) {
            return false;
        }

        if( ! $this->redis && ! $this->redisInit() ) {
            return false;
        }

        $data = array(
            'event' => $event->userFriendly(),
            'to' => $to
            // 'user' => $user->getApiKey()
        );

        return $this->redis->publish('user', json_encode($data));
    }

    public function redisInit()
    {
        $redis = new \Redis();
        if( ! @$redis->connect(env('REDIS_HOST'), env('REDIS_PORT')) ) {
            return $this->redis = false;
        }

        return $this->redis = $redis;
    }

    public function pusherSend($user, $data)
    {
        if( ! $this->pusher ) {
            $this->pusher = $this->pusherInit();
        }
        
        $channel = 'private-user' . $user->getApiKey();

        return $this->pusher->trigger($channel, 'event', $eventData);
    }

    public function pusherInit()
    {
        $app_id = env('PUSHER_ID');
        $app_key = env('PUSHER_KEY');
        $app_secret = env('PUSHER_SECRET');

        $options = array(
            'cluster' => 'eu',
            'encrypted' => true
        );
        return $this->pusher = new \Pusher($app_key, $app_secret, $app_id, $options);
    }
}