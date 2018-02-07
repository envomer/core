<?php

namespace Envo\Mail\Transport;

use Envo\Extension\EmailTemplate\ResponseDTO;
use Envo\Foundation\Loader;
use Envo\Mail\DTO\MessageDTO;
use Envo\Support\File;
use Swift_Mailer;
use Swift_Message;
use Swift_SmtpTransport;

class Smpt implements TransportInterface
{
	/**
	 * @var MessageDTO
	 */
	public $message;
	
	/**
	 * @return ResponseDTO
	 */
	public function send()
	{
		/** @var Loader $autoloader */
		require_once APP_PATH.'vendor/swiftmailer/swiftmailer/lib/swift_required.php';
		
		$host = config('mail.host');
		$username = config('mail.username');
		$password = config('mail.password');
		$port = config('mail.port', 25);
		
		// Create the Transport
		$transport = (new \Swift_SmtpTransport($host, $port))
			->setUsername($username)
			->setPassword($password)
		;
		
		$to = $this->message->to;
		if(!is_array($this->message->to)) {
			$to = [$this->message->to];
		}
		
		$from = [
			$this->message->from => $this->message->from ?: $this->message->fromName
		];
		
		// Create a message
		$message = new \Swift_Message($this->message->subject);
		$message->setFrom($from);
		$message->setTo($to);
		$message->setBody($this->message->body, 'text/html');
		
		if($this->message->attachments) {
			foreach ($this->message->attachments as $attachment) {
				$attach = \Swift_Attachment::fromPath($attachment->path);
				if($attachment->fileName) {
					$attach->setFilename($attachment->fileName);
				}
				$message->attach($attach);
			}
		}
		
		// Send the message
		// Create the Mailer using your created Transport
		$mailer = new \Swift_Mailer($transport);
		$mailLogger = new \Swift_Plugins_Loggers_ArrayLogger();
		$mailer->registerPlugin(new \Swift_Plugins_LoggerPlugin($mailLogger));
		$result = $mailer->send($message);
		
		$response = new ResponseDTO();
		$response->state = $result ? 'success' : 'error';
		
		return $response;
	}
}