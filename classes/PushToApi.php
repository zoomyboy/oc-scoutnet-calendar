<?php

namespace Zoomyboy\Scoutnet\Classes;

use Zoomyboy\Scoutnet\Models\Event;
use Zoomyboy\Scoutnet\Models\Credential;
use Backend\Models\User as BackendUser;

class PushToApi {
    public $credential;
    public $model;

    public function fire($job, $data) {
        $this->credential = Credential::findOrFail($data['credential_id']);

        if (array_has($data, 'event_id')) {
            $this->model = Event::findOrFail($data['event_id']);
            if ($this->model->scoutnet_id) {
                $this->updateEvent();
            }
        }
    }

    public function updateEvent() {
        $api = $this->model->calendar->getApi();
        $this->credential->configure($api);

        $events = $api->get_events_for_global_id_with_filter($this->model->calendar->scoutnet_id, [
            'event_ids' => [$this->model->scoutnet_id]
        ]);

        $event = $events[0];
        
        // Schreibe Event ...
    }
}
