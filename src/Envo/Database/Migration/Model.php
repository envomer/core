<?php

namespace Envo\Database\Migration;

use Envo\AbstractModel;

class Model extends AbstractModel
{
	protected $table = 'core_migrations';
	
	/**
	 * @var string
	 */
	public $migration;
	
	/**
	 * @var int
	 */
	public $batch;
	
	/**
	 * @var string
	 */
	public $migrated_at;
	
	public function initialize()
    {
        $this->table = config('database.migrations', 'core_migrations');
        
        $this->setSource($this->table);
        
        parent::initialize();
    }
}