<?php namespace Zoomyboy\Scoutnetcalendar\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateCalendarsTable extends Migration
{
    public function up()
    {
        Schema::create('zoomyboy_scoutnetcalendar_calendars', function(Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
			$table->string('name');
			$table->integer('scoutnet_id')->unique();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('zoomyboy_scoutnetcalendar_calendars');
    }
}
