<?php

use Envo\AbstractMigration;

class CreateIps extends AbstractMigration
{
    public function up()
    {
    	$this->create('core_ips', function(\Envo\Database\Migration\Table $table) {
			$table->increments('id');
		
			$table->string('country', 64)->nullable();
			$table->string('country_code', 64)->nullable();
			$table->string('region', 64)->nullable();
			$table->string('region_code', 64)->nullable();
			$table->string('city', 64)->nullable();
			$table->string('zip', 64)->nullable();
			$table->string('lat', 64)->nullable();
			$table->string('lon', 64)->nullable();
			$table->string('timezone', 64)->nullable();
			$table->string('isp')->nullable();
			$table->string('org')->nullable();
			$table->string('as')->nullable();
			$table->string('ip', 64)->nullable()->unique();
			$table->integer('user_id')->unsigned()->nullable();
			$table->integer('added_by')->unsigned()->nullable();
		
			$table->timestamp('created_at');
		});
    }

    public function down()
    {
		$this->dropIfExists('core_ips');
    }
}
