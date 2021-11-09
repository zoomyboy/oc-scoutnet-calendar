<?php namespace Zoomyboy\Scoutnet\Updates;

use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;
use Schema;

class CreateCredentialsTable extends Migration
{
    public function up()
    {
        Schema::create('zoomyboy_scoutnet_credentials', function(Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('calendar_id');
            $table->integer('backend_user_id');
            $table->text('data');
            $table->string('connection');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('zoomyboy_scoutnet_credentials');
    }
}
