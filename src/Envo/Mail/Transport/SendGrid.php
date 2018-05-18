<?php

namespace Envo\Mail\Transport;

use Envo\Foundation\Loader;
use Envo\Mail\DTO\MessageDTO;
use Envo\Extension\EmailTemplate\ResponseDTO;

use SendGrid as SendGridMailer;
use SendGrid\Attachment;
use SendGrid\Content;
use SendGrid\Email;
use SendGrid\Mail as SendGridMail;
use SendGrid\MailSettings;
use SendGrid\Personalization;
use SendGrid\SandBoxMode;
use SendGrid\Client;

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
		$loader = resolve('autoloader');
		$loader->loadNamespace([
			Client::class => APP_PATH . 'vendor/sendgrid/php-http-client/lib/SendGrid/'
		]);
		$loader->loadDir(APP_PATH . 'vendor/sendgrid/sendgrid/lib/');
		require_once APP_PATH . 'vendor/sendgrid/sendgrid/lib/helpers/mail/Mail.php';
		require_once APP_PATH . 'vendor/sendgrid/php-http-client/lib/SendGrid/Client.php';
		
		$mail = $this->makeMail();
		$batches = $this->getRecipientBatches();
		
		$mailer = new SendGridMailer($this->token);
		$client = $mailer->client;
		
		$responses = [];
		foreach ($batches as $batch) {
			$mail->personalization = $batch;
			
			$responses[] = $client->mail()->send()->post($mail);
		}

		$response = new ResponseDTO();
		$response->state = $responses ? 'success' : 'error';
		$response->data = $responses;
		
		return $response;
		
		// return $responses;
	}
	
	/**
	 * @return SendGridMail
	 * @throws \Exception
	 */
	public function makeMail()
	{
		$from = new Email($this->message->fromName, $this->message->from);
		
		//$newsletterId = strtotime($newsletter->created_at) . '-' . $newsletter->id;
		
		$content = new Content('text/html', $this->message->body);
		$mail = new SendGridMail();
		$mail->setFrom($from);
		$mail->setSubject($this->message->subject);
		$mail->addContent($content);
		$mail->addCustomArg('newsletterid', 'testing'); // ???
		
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
		
		//die(var_dump($mail));
		
		return $mail;
	}
	
	/**
	 * @return array
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
		
		foreach($to as $email => $name) {
			$personalization = new Personalization();
			
			if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
				$email = $name;
			}

			if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
				internal_exception('validation.givenEmailIsNotValid', 400, [
					'email' => $email,
					'name' => $name
				]);
			}

			$to = new Email(is_string($name) ? $name : $email, $email);
			$personalization->addTo($to);
			// $personalization->addSubstitution('%recipient.name%', $subscriber->subscriber_name);
			$personalization->addSubstitution('%recipient.email%', $email);
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