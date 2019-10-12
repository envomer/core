<?php

class ApplicationTest extends \PHPUnit\Framework\TestCase
{
	public function test_resolves_app()
	{
		$app = new \Envo\Application();
		$app->initialize();
		
		$resolved = resolve('config');
		
		$this->assertEquals(\Envo\Foundation\Config::class, $resolved);
	}
}