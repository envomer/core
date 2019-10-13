<?php

namespace Envo\Mail\Transport;

use Envo\Foundation\Loader;
use Envo\Mail\DTO\MessageDTO;
use Envo\Extension\EmailTemplate\ResponseDTO;

use Envo\Mail\DTO\RecipientDTO;
use SendGrid as SendGridCore;
use SendGrid\Mail\Attachment;
use SendGrid\Mail\MailSettings;
use SendGrid\Mail\Personalization;
use SendGrid\Mail\SandBoxMode;

class SendGrid implements TransportInterface
{
	protected $token;
	
	/**
	 * @var SendGrid
	 */
	protected $sendGrid;
	
	/**
	 * @var MessageDTO
	 */
	public $message;
	
	public $batchSize = 500;
	
	public $simulate = true;
	
	/**
	 * Pushover construct
	 */
	public function __construct()
	{
		$this->validate();
	}
	
	/**
	 * Send notification
	 *
	 * @return \SendGrid\Response[]
	 * @throws \Exception
	 */
	public function send()
	{
		/** @var Loader $loader */
		// $loader = resolve('autoloader');
		// $loader->loadNamespace([
		// 	Client::class => APP_PATH . 'vendor/sendgrid/php-http-client/lib/SendGrid/'
		// ]);
		// $loader->loadDir(APP_PATH . 'vendor/sendgrid/sendgrid/lib/');
		// require_once APP_PATH . 'vendor/sendgrid/sendgrid/lib/helpers/mail/Mail.php';
		// require_once APP_PATH . 'vendor/sendgrid/php-http-client/lib/SendGrid/Client.php';
		
		$batches = $this->getRecipientBatches();

		// die(var_dump($mail, $batches, $this->message));
		
		$mailer = new SendGridCore($this->token);
		
		$responses = [];
		foreach ($batches as $batch) {
			//$mail->personalization = $batch;
			$mail = $this->makeMail($batch);
			
			// TODO validate sendgrid responses
			
			$responses[] = $mailer->send($mail);
		}

		$response = new ResponseDTO();
		$response->state = $responses ? 'success' : 'error';
		$response->data = $responses;
		
		return $response;
		
		// return $responses;
	}
	
	/**
	 * @return \SendGrid\Mail\Mail
	 * @throws \Exception
	 */
	public function makeMail($batch)
	{
		$mail = new \SendGrid\Mail\Mail($this->message->from);
		$mail->setFrom($this->message->from, $this->message->fromName);
		$mail->setSubject($this->message->subject);
		
		if($batch) {
			foreach ($batch as $item) {
				$mail->addPersonalization($item);
			}
		}

		if($this->message->bodyRaw) {
			$mail->addContent('text/plain', $this->message->bodyRaw);
		}

		$mail->addContent('text/html', $this->message->body);
		
		if($this->message->customArguments) {
			$customArguments = is_array($this->message->customArguments) ? $this->message->customArguments : [$this->message->customArguments];

			foreach ($customArguments as $key => $value) {
				// $mail->addCustomArg('newsletterid', 'testing'); // ???
				$mail->addCustomArg($key, is_int($value) ? '' . $value : $value); // ???
			}
		}
		
		if($this->message->attachments) {
			foreach ($this->message->attachments as $file){
				$attachment = new Attachment();
				$attachment->setFilename($file->fileName);
				$attachment->setContent(base64_encode(file_get_contents($file->path)));
				$attachment->setType(mime_content_type($file->path));
				$attachment->setDisposition('attachment');
				$attachment->setContentID($file->fileName);
				$mail->addAttachment($attachment);
			}
		}
		
		if( $this->simulate ) {
			$mail_settings = new MailSettings();
			$sandbox_mode = new SandBoxMode();
			$sandbox_mode->setEnable(true);
			$mail_settings->setSandboxMode($sandbox_mode);
			$mail->setMailSettings($mail_settings); // enable testing
		}
				
		return $mail;
	}
	
	/**
	 * @return array
	 * @throws \Envo\Exception\InternalException
	 * @throws \Envo\Exception\PublicException
	 */
	public function getRecipientBatches()
	{
		$mailingList = [];
		
		/** @var array $to */
		$to = $this->message->to;
		if(is_string($to)) {
			$to = [
				$this->message->to => $this->message->to
			];
		}
		
		/**
		 * Validate emails again
		 */
		if(is_array($to)) {
			$emails = [];
			foreach ($to as $key => $mail) {
				if(is_integer($key)) {
					$key = $mail;
				}
				$emails[$key] = $mail;
			}
			$to = $emails;
		}
		
		if(!$to) {
			public_exception('validation.noEmailIsSet', 400);
		}

		$bccArray = [];
		$bccAdded = false;
		if($this->message->bcc) {
			$bcc = is_string($this->message->bcc) ? [$this->message->bcc] : $this->message->bcc;
			foreach ($bcc as $key => $value) {
				$bccArray[] = new \SendGrid\Mail\Bcc($value, $value);
			}
		}
		
		foreach($to as $email => $name) {
			$recipient = null;
			if(is_a($name, RecipientDTO::class)) {
				$recipient = $name;
				$email = $recipient->email;
				$name = $recipient->name ?: $recipient->email;
			}
			
			if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
				$email = $name;
			}

			if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
				internal_exception('validation.givenEmailIsNotValid', 400, [
					'email' => $email,
					'name' => $name
				]);
			}

			$to = new \SendGrid\Mail\To(is_string($name) ? $name : $email, $email);

			$personalization = new Personalization();
			$personalization->addTo($to);

			if($bccArray && count($bccArray) && !$bccAdded) {
				foreach ($bccArray as $bcc) {
					$personalization->addBcc($bcc);
				}
				$bccAdded = true;
			}

			if($recipient && $recipient->substitutions) {
				foreach ($recipient->substitutions as $key => $value) {
					$personalization->addSubstitution($key, $value);
				}
			}

			// $personalization->addSubstitution('%recipient.name%', $subscriber->subscriber_name);
			// $personalization->addSubstitution('%recipient.email%', $email);
			//$personalization->addSubstitution('%recipient.id%', $subscriber->identifier);
			// SENDGRID doesn't accept numbers. so turn number into string.
			//$personalization->addSubstitution('%r.id%', ''.$subscriber->id);
			//$personalization->addCustomArg('userid', $subscriber->identifier);
			$mailingList[] = $personalization;
		}
		
		return array_chunk($mailingList, $this->batchSize);
	}
	
	
	/**
	 * Validate pushover service
	 *
	 * @return bool
	 * @throws \Envo\Exception\InternalException
	 */
	public function validate()
	{
		if(!env('APP_URL')) {
			internal_exception('app.appUrlNotDefined', 500);
		}
		
		$this->token = env('MAIL_SENDGRID');
		
		if( ! $this->token ) {
			internal_exception('notification.sendGridTokenNotFound', 500);
		}
		
		$this->simulate = env('MAIL_SIMULATE', true);
		
		return true;
	}
}