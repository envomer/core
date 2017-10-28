<?php

namespace Envo\Model;

use Envo\AbstractModel;

/**
 * @property string      name
 * @property integer     id
 * @property string      type
 * @property LegalEntity parent
 */
class LegalEntity extends AbstractModel
{
	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $table = 'core_legal_entities';
	
	/**
	 * @var integer
	 */
	protected $id;
	
	/**
	 * @var string
	 */
	protected $type;
	
	/**
	 * @var LegalEntity
	 */
	protected $parent;
	
	/**
	 * @var string
	 */
	protected $name;
}