<?php

namespace Envo\Mail\DTO;

use Envo\AbstractDTO;

/**
 * Class MessageDTO
 * @package Envo\Mail\DTO
 */
class MessageDTO extends AbstractDTO
{
	/**
	 * @var string
	 */
	public $from;
	
	/**
	 * @var string
	 */
	public $fromName;
	
	/**
	 * @var string
	 */
	public $body;
	
	/**
	 * @var string
	 */
	public $bcc;
	
	/**
	 * @var string
	 */
	public $cc;
	
	/**
	 * @var
	 */
	public $to;
	
	/**
	 * @var string
	 */
	public $subject;
	
	/**
	 * @var string
	 */
	public $layout;
	
	/**
	 * @var AttachmentDTO[]
	 */
	public $attachments;
	
	/**
	 * @param string $path
	 * @param string $fileName
	 */
	public function attachFile($path, $fileName = null)
	{
		$this->attachments[] = new AttachmentDTO([
			'path' => $path,
			'fileName' => $fileName
		]);
	}
}