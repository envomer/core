<?php

namespace Envo\Model;

use Envo\AbstractModel;

/**
 * Class LegalEntities
 *
 * @package Envo\Model
 *
 * @property LegalEntity child
 * @property LegalEntity parent
 */
class LegalEntities extends AbstractModel
{
	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $table = 'core_legal_entities_self';
	
	/**
	 * @var LegalEntity $parent
	 */
	protected $parent;
	
	/**
	 * @var LegalEntity $child
	 */
	protected $child;
	
	/**
	 * initialize the model
	 */
	public function initialize()
	{
		$this->belongsTo('parent_id', LegalEntity::class, 'id', ['alias' => 'parent']);
		$this->belongsTo('child_id', LegalEntity::class, 'id', ['alias' => 'child']);
	}
}