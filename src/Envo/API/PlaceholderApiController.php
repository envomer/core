<?php

namespace Envo\API;

use Envo\AbstractController;
use Envo\Support\Placeholder;
use Phalcon\Mvc\Controller;

class PlaceholderApiController extends AbstractController
{
	public function renderAction()
	{
		/** @var string $size */
		$size = $this->get('size');
		$width = $size;
		$height = $size;
		
		if(strpos($size, 'x') > 0) {
			list($width, $height) = explode('x', $size);
		}
		
		// Get variables from $_GET
		$backgroundColor = isset($_GET['bgColor']) ? strtolower(trim($_GET['bgColor'])) : null;
		$textColor       = isset($_GET['textColor']) ? strtolower(trim($_GET['textColor'])) : null;
		try {
			$placeholder = new Placeholder();
			$placeholder->setWidth($width);
			$placeholder->setHeight($height);
			if ($backgroundColor) $placeholder->setBackgroundColor($backgroundColor);
			if ($textColor) $placeholder->setTextColor($textColor);
			$placeholder->render();
		} catch (\Exception $e){
			die($e->getMessage());
		}
		
		exit;
		
		die(var_dump($height, $width));
	}
}