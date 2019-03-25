<?php namespace Zoomyboy\Scoutnet\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateEventKeywordTable extends Migration
{
    public function up()
    {
        Schema::create('zoomyboy_scoutnet_event_keyword', function(Blueprint $table) {
            $table->engine = 'InnoDB';
			$table->integer('keyword_id');
            $table->string('event_id');
            $table->integer('parent_id')->nullable();
            $table->integer('nest_left')->nullable();
            $table->integer('nest_right')->nullable();
            $table->integer('nest_depth')->nullable();
            $table->index(['event_id', 'keyword_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('zoomyboy_scoutnet_event_keyword');
    }
}
