<?php

namespace Envo\Extension\EmailTemplate;

use Envo\AbstractDTO;

class Style extends AbstractDTO
{
	/**
	 * @var string
	 */
	public $backgroundColor;
	
	/**
	 * @var string
	 */
	public $containerColor;
	
	/**
	 * @var string
	 */
	public $color;
	
	/**
	 * @var string
	 */
	public $btnColor;
	
	/**
	 * @var string
	 */
	public $btnBackgroundColor;
}