<?php namespace Zoomyboy\Scoutnet\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateKeywordsTable extends Migration
{
    public function up()
    {
        Schema::create('zoomyboy_scoutnet_keywords', function(Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
			$table->integer('scoutnet_id');
            $table->string('title');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('zoomyboy_scoutnet_keywords');
    }
}
