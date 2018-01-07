<?php

use Envo\AbstractMigration;
use Envo\Database\Migration\Table;

class CreateExtensionEmailTemplateTable extends AbstractMigration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->create('ex_email_templates', function (Table $table) {
            $table->increments('id');
            
            $table->unsignedInteger('team_id')->index();
            $table->unsignedInteger('user_id')->index();
            $table->string('name');
            $table->string('subject');
            $table->text('body')->nullable();
            $table->tinyInteger('type')->default(1);
            $table->string('from')->nullable();
            $table->string('from_name')->nullable();
            $table->string('bcc');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->dropIfExists('ex_email_templates');
    }
}