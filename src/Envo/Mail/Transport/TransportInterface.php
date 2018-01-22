<?php

namespace Envo\Mail\Transport;

use Envo\Extension\EmailTemplate\ResponseDTO;

interface TransportInterface
{
	/**
	 * @return ResponseDTO
	 */
	public function send();
}