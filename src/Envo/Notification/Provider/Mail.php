<?php

namespace Envo\Notification\Provider;

use Envo\Notification\Notification;
use Envo\Notification\ProviderInterface;
use Envo\Support\File;
use SendGrid;
use SendGrid\Content;
use SendGrid\Email;
use SendGrid\Mail as SendGridMail;
use SendGrid\MailSettings;
use SendGrid\Personalization;
use SendGrid\SandBoxMode;

class Mail implements ProviderInterface
{
	protected $token;
	
	/**
	 * @var SendGrid
	 */
	protected $sendGrid;
	
	public $test = true;
	
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
	 * @param Notification $notification
	 * @return void
	 */
	public function send(Notification $notification)
	{
		$recipients = $this->readRecipients($notification);
		
		$mail = $this->makeMail($notification);
		
		die(var_dump($mail, $recipients));
		$sendGrid = new SendGrid($this->token);
		
	}
	
	/**
	 * @param Notification $notification
	 *
	 * @return SendGridMail
	 * @throws \Exception
	 */
	public function makeMail(Notification $notification)
	{
		$from = new Email($notification->from, $notification->from);
		
		//$newsletterId = strtotime($newsletter->created_at) . '-' . $newsletter->id;
		
		$content = File::render(ENVO_PATH . 'View/html/notification-mail.php', [
			'notification' => $notification
		]);
		
		$content = new Content('text/html', $content);
		$mail = new SendGridMail();
		$mail->setFrom($from);
		$mail->setSubject($notification->subject);
		$mail->addContent($content);
		$mail->addCustomArg('newsletterid', 'testing'); // ???
		
		if( $this->test ) {
			$mail_settings = new MailSettings();
			$sandbox_mode = new SandBoxMode();
			$sandbox_mode->setEnable(true);
			$mail_settings->setSandboxMode($sandbox_mode);
			$mail->setMailSettings($mail_settings); // enable testing
		}
		
		return $mail;
	}
	
	public function readRecipients(Notification $notification)
	{
		$mailingList = [];
		
		$recipients = ['om@envo.me'];
		
		foreach($recipients as $recipient) {
			$personalization = new Personalization();
			$to = new Email(null, $recipient);
			$personalization->addTo($to);
			// $personalization->addSubstitution('%recipient.name%', $subscriber->subscriber_name);
			$personalization->addSubstitution('%recipient.email%', $recipient);
			//$personalization->addSubstitution('%recipient.id%', $subscriber->identifier);
			// SENDGRID doesn't accept numbers. so turn number into string.
			//$personalization->addSubstitution('%r.id%', ''.$subscriber->id);
			//$personalization->addCustomArg('userid', $subscriber->identifier);
			$mailingList[] = $personalization;
		}
		
		return $mailingList;
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
		
		return true;
	}
}