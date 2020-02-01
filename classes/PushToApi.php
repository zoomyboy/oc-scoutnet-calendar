<?php

namespace Zoomyboy\Scoutnet\Classes;

use Zoomyboy\Scoutnet\Models\Event;
use Zoomyboy\Scoutnet\Models\Credential;
use Backend\Models\User as BackendUser;

class PushToApi {
    public $credential;
    public $model;

    public function fire($job, $data) {
        $event = Event::where('id', $data['event_id'])->withIsAllDay()->first();
        $user = BackendUser::find($data['user_id']);

        if (is_null($event)) { return; }

        $event->calendar->connectionService('scoutnet_connect')->saveEvent($event, $user);
        $event->calendar->connectionService('google_calendar')->saveEvent($event, $user);
    }

}
