<?php namespace Zoomyboy\Scoutnet\Updates;

use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;
use Schema;

class CreateCalendarsTable extends Migration
{
    public function up()
    {
        Schema::create('zoomyboy_scoutnet_calendars', function(Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('title')->nullable();
            $table->integer('sort_order')->default(0);
            $table->integer('scoutnet_id')->unique();
            $table->string('provider')->nullable();
            $table->string('aes_key')->nullable();
            $table->string('aes_iv')->nullable();
            $table->text('content');
            $table->string('google_client_id')->nullable();
            $table->string('google_client_secret')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('zoomyboy_scoutnet_calendars');
    }
}
