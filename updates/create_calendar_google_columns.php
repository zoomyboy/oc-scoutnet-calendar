<?php namespace Zoomyboy\Scoutnet\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateCalendarGoogleColumns extends Migration
{
    public function up()
    {
        Schema::table('zoomyboy_scoutnet_calendars', function(Blueprint $table) {
            $table->string('google_client_id')->nullable();
            $table->string('google_client_secret')->nullable();
        });
    }

    public function down()
    {
        Schema::table('zoomyboy_scoutnet_calendars', function(Blueprint $table) {
            $table->dropColumn('google_client_id');
            $table->dropColumn('google_client_secret');
        });
    }
}
