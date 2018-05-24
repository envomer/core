<?php

namespace Envo\Extension\EmailTemplate;

use Envo\AbstractDTO;
use Envo\Mail\DTO\MessageDTO;

class Template extends AbstractDTO
{
	/**
	 * Cerberus is the default template (for now)
	 *
	 * @var string
	 */
	public $template = 'cerberus';
	public $logo;
	public $sections = [];
	public $footer;
	public $unsubscribe;
	public $excerpt;
	public $style;

	public $pixelPath;
	
	/**
	 * @todo implement the option to override template
	 *
	 * @param $name
	 */
	public function setTemplate($name)
	{
		$this->template = $name;
	}
	
	/**
	 * @return string
	 */
	public function render()
	{
		ob_start();
		require_once __DIR__ . '/View/'.$this->template.'/base.php';
		$content = ob_get_contents();
		ob_end_clean();
		
		return $content;
	}
	
	/**
	 * @param Section $section
	 */
	public function parse(Section $section)
	{
		include __DIR__ . '/View/'.$this->template.'/components/'.$section->type.'.php';
	}
	
	/**
	 * Add space
	 */
	public function space()
	{
		$this->sections[] = new Section([
			'type' => Section::TYPE_CLEAR_SPACE
		]);
	}
	
	/**
	 * @param $key
	 * @param $default
	 *
	 * @return mixed
	 */
	public function getStyle($key, $default)
	{
		return ($this->style && $this->style->$key) ? $this->style->$key : $default;
	}

	public static function fromMessageDTO(MessageDTO $message)
	{
		$body = json_decode($message->body);
		$template = new self();
		foreach($body as $section) {
			$section = new Section($section);
			$template->sections[] = $section;
		}

		return $template;
	}
}