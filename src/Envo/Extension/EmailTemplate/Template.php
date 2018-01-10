<?php

namespace Envo\Extension\EmailTemplate;

use Envo\AbstractDTO;

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
		require_once ENVO_PATH . 'Envo/View/html/email/'.$this->template.'/base.php';
		$content = ob_get_contents();
		ob_end_clean();
		
		return $content;
	}
	
	/**
	 * @param Section $section
	 */
	public function parse(Section $section)
	{
		include ENVO_PATH . 'Envo/View/html/email/'.$this->template.'/components/'.$section->type.'.php';
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
}