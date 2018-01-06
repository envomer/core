<?php

namespace Envo\Extension\EmailTemplate;

use Envo\AbstractAPI;

class API extends AbstractAPI
{
	public function init()
	{
		$this->model = TemplateModel::class;
	}
	
	
	public function index()
	{
		die(var_dump('aksdhfkajhf'));
	}
}