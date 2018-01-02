<?php

namespace Envo\API;

use Envo\AbstractController;
use Envo\Support\Placeholder;

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
		$backgroundColor = isset($_GET['bg']) ? strtolower(trim($_GET['bg'])) : null;
		$textColor       = isset($_GET['color']) ? strtolower(trim($_GET['color'])) : null;
		try {
			$placeholder = new Placeholder();
			$placeholder->setWidth($width);
			$placeholder->setHeight($height);
			if ($backgroundColor) {
				$placeholder->setBackgroundColor($backgroundColor);
			}
			if ($textColor) {
				$placeholder->setTextColor($textColor);
			}
			$placeholder->render();
		} catch (\Exception $e){
			die($e->getMessage());
		}
		
		exit;
	}
}