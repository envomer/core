<?php

namespace Envo\Extension\EmailTemplate;

use Envo\AbstractDTO;

class ResponseDTO extends AbstractDTO
{
	/**
	 * @var string
	 */
	public $state;
	
	/**
	 * @var string[]
	 */
	public $messages;
	
	/**
	 * @var mixed
	 */
	public $data;
}