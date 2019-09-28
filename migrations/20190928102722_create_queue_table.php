<?php

use Envo\AbstractMigration;
use Envo\Database\Migration\Table;

class CreateQueueTable extends AbstractMigration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		/**
		 * Create queue table
		 */
        $this->create('core_queue_jobs', static function (Table $table) {
            $table->increments('id');
            
            $table->string('name')->nullable();
            $table->string('queue')->nullable();
            
            $table->integer('type_id')->unsigned();
            
            $table->tinyInteger('status');
            $table->boolean('done')->default(0);
            $table->tinyInteger('attempts')->default(0);
            
            $table->longText('payload')->nullable();
            $table->longText('exception')->nullable();
	
			$table->integer('created_at')->unsigned()->nullable();
			$table->integer('available_at')->unsigned()->nullable();
			$table->integer('reserved_at')->unsigned()->nullable();
			$table->integer('failed_at')->unsigned()->nullable();
        });
	
		/**
		 * Create queue type table
		 */
        $this->create('core_queue_job_types', static function(Table $table) {
        	$table->increments('id');
        	
        	$table->string('class_namespace');
        	$table->tinyInteger('status')->nullable();
        	
        	$table->timestamp('created_at');
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->dropIfExists('core_queue_jobs');
        $this->dropIfExists('core_queue_job_types');
    }
}