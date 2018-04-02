<?php

namespace Envo\Model;

use Envo\AbstractModel;

/**
 * Class Module
 *
 * @package Envo\Model
 *
 * @property integer id
 * @property string  name
 * @property string  slug
 */
class Module extends AbstractModel
{
	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $table = 'core_modules';
	
	/**
	 * @var integer
	 */
	protected $id;
	
	/**
	 * @var string
	 */
	protected $slug;
	
	/**
	 * @var  string
	 */
	protected $name;
	
	/**
	 * @return int
	 */
	public function getId() : int
	{
		return $this->id;
	}
	
	/**
	 * @param int $id
	 */
	public function setId( int $id )
	{
		$this->id = $id;
	}
	
	/**
	 * @return string
	 */
	public function getSlug() : string
	{
		return $this->slug;
	}
	
	/**
	 * @param string $slug
	 */
	public function setSlug( string $slug )
	{
		$this->slug = $slug;
	}
	
	/**
	 * @return string
	 */
	public function getName() : string
	{
		return $this->name;
	}
	
	/**
	 * @param string $name
	 */
	public function setName( string $name )
	{
		$this->name = $name;
	}
}