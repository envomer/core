<?php

namespace Envo\Extension\EmailTemplate;

use Envo\AbstractDTO;

class Section extends AbstractDTO
{
	const TYPE_HERO_IMAGE = 'hero-image';
	const TYPE_CLEAR_SPACE = 'clear-space';
	const TYPE_BACKGROUND_IMAGE_WITH_TEXT = 'background-image-with-text';
	const TYPE_ONE_COLUMN_TEXT = 'one-column-text';
	const TYPE_ONE_COLUMN_TEXT_WITH_LINK = 'one-column-text-with-link';
	const TYPE_THREE_EVEN_COLUMNS = 'three-even-columns';
	const TYPE_THUMBNAIL_RIGHT_TEXT_LEFT = 'thumbnail-right-text-left';
	const TYPE_THUMBNAIL_LEFT_TEXT_RIGHT = 'thumbnail-left-text-right';
	const TYPE_TWO_EVEN_COLUMNS = 'two-even-columns';
	
	/**
	 * @var string
	 */
	public $type;
	
	/**
	 * @var string[]
	 */
	public $images;
	
	/**
	 * @var string
	 */
	public $title;
	
	/**
	 * @var string[]
	 */
	public $paragraphs;
	
	/**
	 * @var string
	 */
	public $link;
	
	/**
	 * @var string
	 */
	public $linkTitle;
	
	/**
	 * @var string
	 */
	public $align;
	
	/**
	 * @var string[]
	 */
	public $style;
	
	public function getStyle($key, $default = null)
	{
		$value = ($this->style && isset($this->style->$key)) ? $this->style->$key : $default;
		
		// Refactor
		if(!$value) {
			// check what kind of theme
			$cerberus = [
				'backgroundColor' => '#e9ecef'
			];
			
			
			if(isset($cerberus[$key])) {
				$value = $cerberus[$key];
			}
		}
		
		if($value && $key === 'borderWidth' && strpos($value, 'px') === false) {
			$value .= 'px';
		}
		
		return $value;
	}
}