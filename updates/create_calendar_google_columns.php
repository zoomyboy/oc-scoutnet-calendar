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

        Schema::table('zoomyboy_scoutnet_credentials', function(Blueprint $table) {
            $table->dropColumn('api_key');
            $table->dropColumn('user');
            $table->dropColumn('time');
            $table->dropColumn('firstname');
            $table->dropColumn('surname');
            $table->text('data');
            $table->string('connection');
        });
    }

    public function down()
    {
        Schema::table('zoomyboy_scoutnet_calendars', function(Blueprint $table) {
            $table->dropColumn('google_client_id');
            $table->dropColumn('google_client_secret');
        });

        Schema::table('zoomyboy_scoutnet_credentials', function(Blueprint $table) {
            $table->string('api_key', 32);
            $table->string('user');
            $table->string('time');
            $table->string('firstname');
            $table->string('surname');
            $table->dropColumn('data');
            $table->dropColumn('connection');
        });
    }
}
