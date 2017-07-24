<?php

namespace Envo;

use JsonSerializable;

use Envo\Support\Arr;

class AbstractDTO implements JsonSerializable
{
	/**
	 * Contruct abstract DTO
	 *
	 * @param object $data
	 * @param array $mapping
	 */
	public function __construct($data = null, $mapping = null)
	{
		if( ! $data ) {
			return true;
		}

		if(is_string($data)) {
			$data = ($decoded = json_decode($data)) ? $decoded : null;
		}
	    
		if(is_array($data) && !empty($data) && array_values($data) === $data) {
			$data = $data[0];
		}

		if( is_a($data, AbstractModel::class) ) {
			$data = Arr::getPublicProperties($data);
		}

		if( ! is_array($data) ) {
			return true;
		}

		foreach ($data as $k => $v) {
			if (property_exists($this, $k)) {
                $this->{$k} = $v;
            }
            else if($mapping && isset($mapping[$k])) {
            	// if( strpos(haystack, needle) )
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
		return Arr::getPublicProperties($this);
	}

	/**
	 * Return array
	 *
	 * @return void
	 */
	public function toArray()
	{
		return (array) $this;
	}
}