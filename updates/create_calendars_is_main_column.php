<?php

namespace Zoomyboy\Scoutnet\Updates;

use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;
use Schema;

class CreateCalendarsIsMainColumn extends Migration
{
    public function up()
    {
        Schema::table('zoomyboy_scoutnet_calendars', function (Blueprint $table) {
            $table->boolean('is_main')->default(false);
        });
    }

    public function down()
    {
        Schema::table('zoomyboy_scoutnet_calendars', function (Blueprint $table) {
            $table->dropColumn('is_main');
        });
    }
}
