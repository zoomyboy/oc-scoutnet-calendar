<?php namespace Zoomyboy\Scoutnet\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateEventsTable extends Migration
{
    public function up()
    {
        Schema::create('zoomyboy_scoutnet_events', function(Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('title');
            $table->string('location')->nullable();
            $table->datetime('starts_at');
            $table->datetime('ends_at')->nullable();
            $table->string('organizer')->nullable();
            $table->string('target')->nullable();
            $table->string('url')->nullable();
            $table->string('url_text')->nullable();
            $table->text('description')->nullable();
            $table->integer('calendar_id');
            $table->integer('scoutnet_id')->nullable()->unique();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('zoomyboy_scoutnet_events');
    }
}
