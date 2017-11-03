<?php
namespace Tests\Unit;

use Envo\Model\User;
use Tests\UnitTestCase;

class CoreTest extends UnitTestCase
{
	public function testTestCase()
	{
		$this->assertEquals(
			"works",
			"works",
			"This is OK"
		);
		
		$this->assertEquals(
			"works",
			"works1",
			"This will fail"
		);
	}
}