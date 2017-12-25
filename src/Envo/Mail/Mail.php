<?php

namespace Envo\Mail;

use Envo\Mail\DTO\MessageDTO;
use Envo\Mail\Transport\SendGrid;
use Envo\Mail\Transport\Smpt;
use Envo\Mail\Transport\TransportInterface;

class Mail
{
	/**
	 * @var MessageDTO
	 */
	public $message;
	
	/**
	 * @var string
	 */
	public $driver;
	
	/**
	 * @var TransportInterface
	 */
	public $transport;
	
	/**
	 * Mail constructor.
	 *
	 * @param MessageDTO|null $message
	 * @param string            $driver
	 */
	public function __construct(MessageDTO $message = null, $driver = null)
	{
		$this->message = $message;
		$this->driver = $driver ?: config('mail.driver');
	}
	
	/**
	 * @param string $driver
	 *
	 * @throws \Envo\Exception\InternalException
	 */
	public function setDriver($driver)
	{
		if(!$this->getDrivers($driver)) {
			internal_exception('email.driverNotFound', 404);
		}
		
		$this->driver = $driver;
	}
	
	/**
	 * @return mixed
	 * @throws \Envo\Exception\InternalException
	 * @throws \Envo\Exception\PublicException
	 */
	public function send()
	{
		if(!$this->transport) {
			$this->setTransport();
		}
		
		$this->transport->message = $this->message;
		
		return $this->transport->send();
	}
	
	/**
	 * @return TransportInterface
	 *
	 * @throws \Envo\Exception\InternalException
	 * @throws \Envo\Exception\PublicException
	 */
	public function setTransport()
	{
		if(!$this->driver) {
			internal_exception('email.driverIsNotSet', 500);
		}
		
		$driver = $this->getDrivers($this->driver);
		
		if(!$driver) {
			public_exception('email.transportNotFound', 404);
		}
		
		return $this->transport = new $driver();
	}
	
	/**
	 * @param null $driver
	 *
	 * @return array
	 */
	public function getDrivers($driver = null)
	{
		$drivers = [
			'smtp' => Smpt::class,
			'sendgrid' => SendGrid::class
		];
		
		if($driver) {
			if(!array_key_exists($driver, $drivers)) {
				return null;
			}
			
			return $drivers[$driver];
		}
		
		return $drivers;
	}
}