<?php
/**
 * Created by PhpStorm.
 * User: envo
 * Date: 14.12.17
 * Time: 07:11
 */

namespace Envo\Mail\Transport;


use Envo\Foundation\Loader;
use Envo\Support\File;
use Swift_Mailer;
use Swift_Message;
use Swift_SmtpTransport;

class Smpt
{
	public function send()
	{
		/** @var Loader $autoloader */
		require_once APP_PATH.'vendor/swiftmailer/swiftmailer/lib/swift_required.php';
		
		$host = env('MAIL_HOST');
		$username = env('MAIL_USERNAME');
		$password = env('MAIL_PASSWORD');
		$port = env('MAIL_PORT', 25);
		// Create the Transport
		$transport = (new Swift_SmtpTransport($host, $port))
			->setUsername($username)
			->setPassword($password)
		;
		
		// Create the Mailer using your created Transport
		$mailer = new Swift_Mailer($transport);
		
		// Create a message
		$message = (new Swift_Message('Wonderful Subject'))
			->setFrom(['john@doe.com' => 'John Doe'])
			->setTo(['receiver@domain.org', 'other@domain.org' => 'A name'])
			->setBody('Here is the message itself')
		;
		
		// Send the message
		$result = $mailer->send($message);
		
		die(var_dump($result));
	}
}