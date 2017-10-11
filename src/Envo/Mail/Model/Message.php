<?php 

namespace Envo\Mail\Model;

use Envo\Mail\NewsletterService;

class Newsletter extends \Envo\AbstractModel
{
	protected $table = 'core_mails';

	protected $softDeletes = true;

	const STATUS_FAILED = -1;
	const STATUS_SEND = 1;
	const STATUS_DRAFT = 2;
	const STATUS_QUEUE = 5;
	const STATUS_SENT = 10;

	public function initialize()
	{
		$this->hasMany('id', NewsletterSent::class, 'newsletter_id', array('alias' => 'sent'));
	}

	public function index($attributes, $user, $data)
	{
		$conditions = 'FROM ' . self::class . ' n';
		$conditions .= ' LEFT JOIN ' . NewsletterSent::class . ' ns ON ns.newsletter_id = n.id';
		$conditions .= ' WHERE n.deleted_at IS NULL AND n.client_id = :client_id:';

		$normal = 'n.id, SUM(ns.views) count_views, COUNT(ns.failed_at) count_failed, COUNT(ns.sent_at) count_sent, COUNT(ns.delivered_at) count_delivered,';
		$normal .= ' n.subject, n.[from], n.[from_name], n.status, n.scheduled_at, n.created_at, n.updated_at,';
		$normal .= ' SUM(IF(ns.views > 0, 1,0)) as count_views_unique';
		$normal .= ' ' .$conditions;
		$normal .= ' GROUP BY n.id';
		$normal .= ' ORDER BY n.id DESC';

		return array(
			'query' => 'SELECT ' . $normal . ' LIMIT :limit: OFFSET :offset:',
            'count' => 'SELECT COUNT(n.id) cnt ' . $conditions,
			'bind' => array('client_id' => $user->client_id),
		);
	}

	public function preSave($user, $data)
	{
		if( ! isset($this->id) ) {
			$this->user_id = $user->id;
			$this->client_id = $user->client_id;

			$this->created_at = \Date::now();
		}
		else {
			if( $this->status == self::STATUS_SENT ) {
				return \_t('Newsletter wurde schon verschickt. Kann nicht nochmal verschickt werden');
			}
		}

		if( isset($data['status']) && $data['status'] != self::STATUS_SENT ) {
			$this->status = $data['status'];
		}
	}

	public function postSave($user, $data)
	{
		if( isset($data['subscribers']) && $data['subscribers'] ) {
			$sent = $this->sent;
			if(! $sent) $sent = array();

			$sentArr = is_array($sent) ? $sent : $sent->toArray();
			$inNewsletter = array_flip(array_map(function($sub) { return $sub['subscriber_id']; }, $sentArr ));

			$newSubscribers = array_filter($data['subscribers'], function($val) use ($inNewsletter) { return isset($inNewsletter[$val]) === false; });

			if( $newSubscribers && ($newSubscriberElements = SubscriberRepository::getAllById($newSubscribers, array('id', 'email'))) ) {
				$newSubscriberElements = $newSubscriberElements->toArray();

				$insert = array();
				$now = \Date::now();
				$time = microtime(true);
				foreach ($newSubscriberElements as $key => $sub) {
					$insert[] = array(
						'subscriber_id' => $sub['id'],
						'subscriber_email' => $sub['email'],
						'newsletter_id' => $this->id,
						'created_at' => $now,
						'identifier' => $this->id .'.'. $time . '-' . $key
					);
				}

				$batch = new \BatchInsert('cc_newsletter_sent');
	            $batch->columns = ['subscriber_id', 'subscriber_email', 'newsletter_id', 'created_at', 'identifier'];
	            $batch->data = $insert;
	            $batch->insert();
			}

			$t = array_flip($data['subscribers']);
			foreach ($sent as $key => $sub) {
				if( ! isset($t[$sub->subscriber_id]) ) {
					$sub->delete();
				}
			}
		}
		else if($sent = $this->sent){
			foreach ($sent as $key => $sub) {
				$sub->delete();
			}
		}

		// Find a way to retrieve the sent again.
		// if( isset($sent) ) $this->sent = null;

		//if( isset($data['status']) && $data['status'] == self::STATUS_SEND && $this->status != self::STATUS_SENT ) {
		//	$response = NewsletterService::sendNewsletter($this);
		//	if( ! is_bool($response) || ! $response ) return array('id' => $this->id, 'msg' => $response, 'success' => 0);
		//}

		return array('id' => $this->id);
	}

	public function setStatus($status)
	{
		$this->status = $status;
	}
}