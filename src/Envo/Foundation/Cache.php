<?php

namespace Envo\Foundation;

use Phalcon\Cache\Adapter\Stream as BackFile;
use Phalcon\Cache\Frontend\Data as FrontData;

class Cache
{
	protected $cache;
	
	/**
	 */
	public function __construct()
	{
		$frontCache = new FrontData(
		    array(
				/** TODO add to config file **/
				'lifetime' => 172800 // 2 days
		    )
		);

		$this->cache = new BackFile(
		    $frontCache,
		    array(
				'prefix'   => 'cache_',
				'cacheDir' => APP_PATH . 'storage/framework/cache/'
		    )
		);
	}
	
	/**
	 * @param $key
	 * @param null $lifeTime
	 * @param null $defaultValue
	 *
	 * @return mixed|null
	 */
	public function get($key, $lifeTime = null, $defaultValue = null)
	{
		$value = $this->cache->get($key);
		if(!$value) {
			$value = \is_callable($defaultValue) ? $defaultValue() : $defaultValue;
			
			if($lifeTime) {
				$this->set($key, $value, $lifeTime);
			}
		}
		
		return $value;
	}
	
	/**
	 * @param $key
	 * @param $value
	 * @param null $lifeTime
	 *
	 * @return void
	 */
	public function set($key, $value, $lifeTime = null)
	{
		$this->cache->save($key, $value, $lifeTime);
	}
	
	/**
	 * @param $key
	 *
	 * @return void
	 */
	public function delete($key)
	{
		$this->cache->delete($key);
	}
	
	/**
	 * @param null $prefix
	 *
	 * @return array
	 */
	public function keys($prefix = null): array
	{
		return $this->cache->queryKeys($prefix);
	}
	
}
