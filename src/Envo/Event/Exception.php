<?php

namespace Envo\Event;

use Envo\AbstractEvent;

class Exception extends AbstractEvent
{
    /**
     * Get description
     *
     * @return void
     */
	public function getDescription()
	{
		if( $this->event->user_id ) {
			$user = $this->event->user->username;
		} else {
			$user = $this->event->ip_id ? $this->event->ip->ip : 'unbekannt';
		}
		return $user . ' caused an exception';
	}

    /**
     * Send event via
     *
     * @return void
     */
	public function via()
	{
		return ['notification', 'pushover'];
	}

    /**
     * Send to
     *
     * @return void
     */
	public function to()
	{
		// how? just determine user ids
		return [
			'level' => 90,

			// 'users' => [] // user ids
			// 'teams' => [] // user ids
			// 'team' => true, // the team the user is in...'true' OR 'permission' int
			// 'self' => true // just the creator
		];
	}
}