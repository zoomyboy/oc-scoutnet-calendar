<?php namespace Zoomyboy\Scoutnet\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateCalendarsGroupColumn extends Migration
{
    public function up()
    {
        Schema::table('zoomyboy_scoutnet_calendars', function(Blueprint $table) {
            $table->string('group')->nullable();
        });
    }

    public function down()
    {
        Schema::table('zoomyboy_scoutnet_calendars', function(Blueprint $table) {
            $table->dropColumn('group');
        });
    }
}
