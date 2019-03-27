<?php

use Input;

Route::get('/scoutnet-export/ical/calendar.ical', function() {
    return app('scoutnet.ical')->output(Input::get('filter') ?: []);
});
