<?php

namespace Envo\Extension\EmailTemplate;

use Envo\AbstractAPI;
use Phalcon\Mvc\Model\Query\Builder;

class API extends AbstractAPI
{
	public function init()
	{
		$this->model = TemplateModel::class;
	}
	
	/**
	 * @return bool
	 */
	public function authorize()
	{
		return $this->user && $this->user->isLoggedIn();
	}
	
	
	/**
	 * @param Builder $builder
	 */
	public function index($builder)
	{
		$builder->where('t.team_id = :team_id:', [
			'team_id' => $this->user->getTeamId()
		]);
	}
}