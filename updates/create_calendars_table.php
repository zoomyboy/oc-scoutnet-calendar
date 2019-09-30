<?php namespace Zoomyboy\Scoutnet\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

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
            $table->text('content');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('zoomyboy_scoutnet_calendars');
    }
}
