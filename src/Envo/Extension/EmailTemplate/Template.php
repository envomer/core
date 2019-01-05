<?php

namespace Envo\Extension\EmailTemplate;

use Envo\AbstractDTO;
use Envo\Extension\BBCode\BBCode;
use Envo\Mail\DTO\MessageDTO;

class Template extends AbstractDTO
{
	/**
	 * Cerberus is the default template (for now)
	 *
	 * @var string
	 */
	public $template = 'cerberus';
	
	/**
	 * @var string
	 */
	public $logo;
	
	/**
	 * @var Section[]
	 */
	public $sections = [];
	
	/**
	 * @var string
	 */
	public $footer;
	
	/**
	 * @var string
	 */
	public $unsubscribe;
	
	/**
	 * @var string
	 */
	public $excerpt;
	
	/**
	 * @var string
	 */
	public $style;
	
	/**
	 * @var string
	 */
	public $pixelPath;
	
	/**
	 * @var BBCode
	 */
	public $bbCode;
	
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
		//$bbCode = $this->getBBCode();
		
		ob_start();
		include __DIR__ . '/View/'.$this->template.'/base.php';
		$content = ob_get_contents();
		ob_end_clean();
		
		return $content;
	}
	
	/**
	 * @param Section $section
	 */
	public function parse(Section $section)
	{
		$bbCode = $this->getBBCode();
		
		$section->title = $bbCode->render($section->title);
		if($section->paragraphs) {
			foreach ($section->paragraphs as $i => $paragraph) {
				$section->paragraphs[$i] = $bbCode->render($section->paragraphs[$i], true, 0);
			}
		}
		
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
	public function getStyle($key, $default = null)
	{
		$value = ($this->style && $this->style->$key) ? $this->style->$key : $default;
		
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
		
		return $value;
	}
	
	/**
	 * @param Section $section
	 *
	 * @return void
	 */
	public function addSection(Section $section)
	{
		$this->sections[] = $section;
	}
	
	/**
	 * @return string
	 */
	public function renderRaw()
	{
		$raw = '';
		foreach ($this->sections as $section) {
			if($section->title) {
				$raw .= $section->title . "\n";
			}

			if($section->link) {
				$raw .= $section->link . "\n";
			}

			if($section->paragraphs) {
				$raw .= implode("\n", $section->paragraphs) . "\n";
			}
		}

		return trim(strip_tags($raw));
	}
	
	/**
	 * @param MessageDTO $message
	 *
	 * @return Template
	 */
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
	
	/**
	 * @return BBCode
	 */
	public function getBBCode()
	{
		if(!$this->bbCode) {
			$this->bbCode = new BBCode();
		}
		
		return $this->bbCode;
	}
}