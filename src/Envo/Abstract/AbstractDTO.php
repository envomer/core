<?php

namespace Envo;

use JsonSerializable;
use Envo\Support\Arr;

/**
 * Class AbstractDTO
 * @package Envo
 */
class AbstractDTO implements JsonSerializable
{
	/**
	 * Construct an abstract DTO
	 *
	 * @param mixed $data
	 * @param array $mapping
	 */
	public function __construct($data = null, $mapping = null)
	{
		if( ! $data ) {
			return;
		}
		
		if(is_string($data)) {
			$data = ($decoded = json_decode($data)) ? $decoded : null;
		}
		
		if(is_array($data) && !empty($data) && array_values($data) === $data) {
			$data = $data[0];
		}
		
		if( is_a($data, AbstractModel::class) ) {
			$data = get_object_vars($data);
		}
		
		if(!$mapping) {
			$mapping = $this->getMapping();
		}
		
		foreach ($data as $k => $v) {
			if (property_exists($this, $k)) {
				$this->{$k} = $v;
			}
			else if($mapping && isset($mapping[$k])) {
				$this->{$mapping[$k]} = $v;
			}
		}
	}

	/**
	 * Json encode object
	 *
	 * @return string
	 */
	public function __toString()
	{
		return json_encode($this);
	}

	/**
	 * Serialize object
	 *
	 * @return array
	 */
	public function jsonSerialize()
	{
		$data = get_object_vars($this);
		
		/** @var array $mapping */
		$mapping = $this->getMapping();
		if($mapping) {
			foreach ($mapping as $key => $value) {
				if(!property_exists($this, $value)) {
					continue;
				}
				$data[$key] = $data[$value];
				unset($data[$value]);
			}
		}
		
		return $data;
	}

	/**
	 * Return array
	 *
	 * @return array
	 */
	public function toArray()
	{
		return (array) $this;
	}
	
	/**
	 * @return bool|array
	 */
	public function getMapping()
	{
		return false;
	}
}