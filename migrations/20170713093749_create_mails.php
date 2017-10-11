<?php

use Envo\AbstractMigration;

class CreateMails extends AbstractMigration
{
    public function up()
    {
        if(!config('app.mail.enabled')) {
            return false;
        }

        $this->create('core_mails', function($table) {
            $table->increments('id');
            
            $table->string('title');
            $table->text('content'); // json encoded array?
            $table->boolean('draft')->default(1);
            $table->boolean('sent')->default(0);

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
