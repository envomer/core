<?php

namespace Envo\Command;

use Envo\Support\File;
use Envo\Support\Str;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleOutput;


class MainTask extends \Phalcon\Cli\Task
{
	public function mainAction()
	{
		$table = new Table(new ConsoleOutput());

		$headers = ['Commands'];
		$rows = [];

		$finder = File::files(__DIR__ . '*');

		foreach ($finder as $key => $file) {
			$filename = str_replace('Task.php', '', $file);
			$filename = str_replace(__DIR__ .'/', '', $filename);
			
			$methods = array();

			if( $filename == 'Main' ) {
				continue;
			}

			$rows[] = array(Str::snake($filename,'-'));
		}

		$table->setHeaders($headers)->setRows($rows)->setStyle('compact')->render();
	}
}