<?php namespace Zoomyboy\Scoutnet\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateGoogleEventsTable extends Migration
{
    public function up()
    {
        Schema::create('zoomyboy_google_events', function(Blueprint $table) {
            $table->integer('event_id');
            $table->integer('credential_id');
            $table->string('google_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('zoomyboy_google_events');
    }
}
