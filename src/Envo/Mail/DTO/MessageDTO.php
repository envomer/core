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
	public $bodyRaw;
	
	/**
	 * @var string
	 */
	public $bcc;
	
	/**
	 * @var string
	 */
	public $cc;
	
	/**
	 * @var string|array|RecipientDTO[]
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
	 * @var string
	 */
	public $footer;
	
	/**
	 * @var []
	 */
	public $customArguments;
	
	/**
	 * MessageDTO constructor.
	 *
	 * @param array|\stdClass       $data
	 * @param array|null $mapping
	 */
	public function __construct($data = null, array $mapping = null)
	{
		$from = config('mail.from');
		
		$this->from = $from['address'];
		$this->fromName = $from['name'];
		
		parent::__construct($data, $mapping);
	}
	
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