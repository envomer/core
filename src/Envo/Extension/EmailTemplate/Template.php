<?php

namespace Envo\Extension\EmailTemplate;

use Envo\AbstractDTO;
use Envo\Extension\BBCode\BBCode;
use Envo\Extension\Markdown\Markdown;
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
     * @var Style
     */
    public $style;
    
    /**
     * @var string
     */
    public $pixelPath;
    
    /**
     * @var BBCode
     */
    private $bbCode;
    
    private $markdownEngine;
    
    public $useMarkdown = false;
    
    public function __construct($data = null, array $mapping = null)
    {
        parent::__construct($data, $mapping);
        
        if ($this->style && is_array($this->style)) {
            $this->style = new Style($this->style);
        }
        
        if (!$this->style) {
            $this->style = new Style();
        }
    }
    
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
        if ($this->useMarkdown) {
            $engine = $this->getMarkdownEngine();
            $method = 'parse';
        } else {
            $engine = $this->getBBCode();
            $method = 'render';
        }
        
        $section->title = $engine->$method($section->title);
        if ($section->paragraphs) {
            foreach ($section->paragraphs as $i => $paragraph) {
                $section->paragraphs[$i] = $engine->$method($section->paragraphs[$i], true, 0);
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
        if (!$value) {
            // check what kind of theme
            $cerberus = [
                //'backgroundColor' => '#e9ecef'
            ];
            
            if (isset($cerberus[$key])) {
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
            if ($section->title) {
                $raw .= $section->title . "\n";
            }

            if ($section->link) {
                $raw .= $section->link . "\n";
            }

            if ($section->paragraphs) {
                $raw .= implode("\n\n", $section->paragraphs) . "\n";
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
        foreach ($body as $section) {
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
        if (!$this->bbCode) {
            $this->bbCode = new BBCode();
        }
        
        return $this->bbCode;
    }
    
    /**
     * @return BBCode
     */
    public function getMarkdownEngine()
    {
        if (!$this->markdownEngine) {
            $this->markdownEngine = Markdown::instance();
        }
        
        return $this->markdownEngine;
    }
    
    /**
     *
     * @see https://stackoverflow.com/questions/3512311/how-to-generate-lighter-darker-color-with-php
     * @param $hex
     * @param $steps
     *
     * @return string
     */
    public function adjustColorBrightness($hex, $steps)
    {
        // Steps should be between -255 and 255. Negative = darker, positive = lighter
        $steps = max(-255, min(255, $steps));
        
        // Normalize into a six character long hex string
        $hex = str_replace('#', '', $hex);
        if (strlen($hex) == 3) {
            $hex = str_repeat(substr($hex, 0, 1), 2).str_repeat(substr($hex, 1, 1), 2).str_repeat(substr($hex, 2, 1), 2);
        }
        
        // Split into three parts: R, G and B
        $color_parts = str_split($hex, 2);
        $return = '#';
        
        foreach ($color_parts as $color) {
            $color   = hexdec($color); // Convert to decimal
            $color   = max(0, min(255, $color + $steps)); // Adjust color
            $return .= str_pad(dechex($color), 2, '0', STR_PAD_LEFT); // Make two char hex code
        }
        
        return $return;
    }
}
