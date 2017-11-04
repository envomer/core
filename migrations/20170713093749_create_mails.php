<?php

use Envo\AbstractMigration;

use Envo\Database\Migration\Table;

class CreateMails extends AbstractMigration
{
    public function up()
    {
        //if(!config('app.mail.enabled', false)) {
        //    return false;
        //}

        $this->create('core_mails', function(Table $table) {
            $table->increments('id');
            
            $table->string('title');
            $table->text('content'); // json encoded array?
            $table->boolean('draft');
            $table->boolean('sent');

            // author (user_id???)
            $table->integer('author_id')->unsigned()->index();
            
            // id of email template
            $table->smallInteger('template_id')->unsigned()->nullable();
			
            $table->softDeletes();
            $table->nullableTimestamps();
        });
    }

    public function down()
    {
        $this->dropIfExists('core_mails');
    }
}
