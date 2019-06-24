<?php

namespace Envo\Model;

use Phalcon\Crypt;

/**
 * Encrypt columns
 *
 * Trait EncryptColumns
 * @package Envo\Model
 */
trait EncryptColumn
{
	abstract public function getEncryptedColumns();
	
	/**
	 * @return void
	 */
	public function beforeSave()
	{
		/** @var Crypt $crypt */
		$crypt = $this->di->get('crypt');
		
		$columns = $this->getEncryptedColumns();
		
		foreach ($columns as $column) {
			$this->$column = $this->$column ? $crypt->encrypt($this->$column) : null;
		}
	}
	
	/**
	 * @param null $data
	 *
	 * @return mixed|void
	 * @throws Crypt\Mismatch
	 */
	public function afterFetch($data = null)
	{
		/** @var Crypt $crypt */
		$crypt = $this->di->get('crypt');
		
		$columns = $this->getEncryptedColumns();
		
		if($data) {
			// what??
			foreach ($columns as $column) {
				if(!isset($data[$column])) {
					continue;
				}
				$data[$column] = $data[$column] ? $crypt->decrypt($data[$column]) : null;
			}
			
			return $data;
		}
		
		foreach ($columns as $column) {
			if(!isset($this->$column)) {
				continue;
			}
			$this->$column = $this->$column ? $crypt->decrypt($this->$column) : null;
		}
	}
	
	public function afterSave()
	{
		$this->afterFetch(); // decrypt data
	}
}