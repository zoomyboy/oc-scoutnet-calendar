<?php namespace Zoomyboy\Scoutnet\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class ChangeEndsAtEventsColumn extends Migration
{
    public function up()
    {
        Schema::table('zoomyboy_scoutnet_events', function(Blueprint $table) {
            $table->datetime('ends_at')->nullable(false)->change();
        });
    }

    public function down()
    {
        Schema::table('zoomyboy_scoutnet_events', function(Blueprint $table) {
            $table->datetime('ends_at')->nullable(true)->change();
        });
    }
}

