<?php 

namespace Envo\Sms;

use Date;
use Envo\AbstractService;

class SmsService extends AbstractService
{
	public static function send(SmsDTO $dto)
	{
		self::autoloader();

		// Normally you would just need to add your access key here,
		// but we'll take care of that for now.
        $key = env(env('SMS_LIVE') ? 'SMS_KEY_LIVE' : 'SMS_KEY_TEST');
        if( !$key ) {
            internal_exceptions('sms.keyNotGiven', 500);
        }

		$messageBird = new \MessageBird\Client($key);

		$recipients = is_array($dto->recipients) ? $dto->recipients : array($dto->recipients);
		$from = str_replace(' ', '', $dto->from);
		
		// Let's build the message
		$message = new \MessageBird\Objects\Message();
		$message->originator = $from;
		$message->recipients = $recipients;
		$message->body = $dto->body;
		$message->reference = Str::identifier();
		if($url = env('APP_URL')) {
            $message->reportUrl = $url . '/service/sms/rapport';
        }

		$response = $messageBird->messages->create($message);

		$msg = new SmsMessage();
		$msg->create([
			'remote_id' => $response->getId(),
			'user_id' => $dto->userId,
			'direction' => $message->direction,
			'type' => $message->type,
			'originator' => $from,
			'body' => $message->body,
			'validity' => $response->validity,
			'reference' => $response->reference,
			'mclass' => $message->mclass,
			'scheduled_at' => date('Y-m-d H:i:s', strtotime($message->scheduledDatetime)),
			'gateway' => $response->gateway,
			'team_id' => $dto->teamId,
			'recipients' => json_encode($recipients),
			'created_at' => Date::now()
		]);

		// Boom! Sent!
		// $data = ['to' => $recipients, 'from' => $from, 'message' => $body];
		new MessageSent(null, true, $msg);
		
		return $msg;
	}
}