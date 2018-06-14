<?php

namespace Envo\Mail\DTO;

use Envo\AbstractDTO;

/**
 * Class MessageDTO
 * @package Envo\Mail\DTO
 */
class RecipientDTO extends AbstractDTO
{
	/**
	 * @var string
	 */
	public $name;
	
	/**
	 * @var string
	 */
	public $email;
	
	/**
	 * @var []
	 */
	public $substitutions;
}