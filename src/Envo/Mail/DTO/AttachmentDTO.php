<?php

namespace Envo\Mail\DTO;

use Envo\AbstractDTO;

class AttachmentDTO extends AbstractDTO
{
	/**
	 * @var string
	 */
	public $path;
	
	/**
	 * @var string
	 */
	public $fileName;
}