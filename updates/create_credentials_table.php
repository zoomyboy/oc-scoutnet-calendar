<?php namespace Zoomyboy\Scoutnet\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateCredentialsTable extends Migration
{
    public function up()
    {
        Schema::create('zoomyboy_scoutnet_credentials', function(Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('calendar_id');
            $table->string('api_key', 32);
            $table->string('user');
            $table->string('time');
            $table->string('firstname');
            $table->string('surname');
            $table->integer('backend_user_id');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('zoomyboy_scoutnet_credentials');
    }
}
