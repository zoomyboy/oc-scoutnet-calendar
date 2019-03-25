<?php namespace Zoomyboy\Scoutnet\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateKeywordTagTable extends Migration
{
    public function up()
    {
        Schema::create('zoomyboy_scoutnet_keyword_tag', function(Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('keyword_id');
            $table->integer('tag_id');
            $table->index(['tag_id', 'keyword_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('zoomyboy_scoutnet_keyword_tag');
    }
}
