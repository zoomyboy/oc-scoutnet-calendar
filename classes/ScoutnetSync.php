<?php

namespace Zoomyboy\Scoutnet\Classes;

use Zoomyboy\Scoutnet\Models\Calendar;
use Zoomyboy\Scoutnet\Exceptions\CalendarNotFoundException;

class ScoutnetSync {
    private $calendar;
    private $sn;

    private function __construct($groupId, $calendar = null) {
        try {
            $this->calendar = $calendar;
            require_once(plugins_path() . '/zoomyboy/scoutnet/vendor/scoutnet-api-client-php/src/scoutnet.php');
            $this->sn = scoutnet()->group($groupId);
        } catch(\SN_Exception_EndUser $e) {
            if (str_contains($e->getMessage(), 'Es gibt kein group')) {
                throw new CalendarNotFoundException();
            }
        }

        return $this;
    }

    public static function fromGroup($groupId, $calendar = null) {
        return new static($groupId, $calendar);
    }

    public function getName() {
        return $this->sn->name;
    }

    public function sync() {
        $this->calendar->update(['title' => $this->getName()]);

        $dateRange = [ date('Y') - 1, date('Y') + 1 ];

        $events = collect($this->sn->events('start_date > "'.$dateRange[0].'-01-01" AND start_date < "'.$dateRange[1].'-12-31"'))->each(function($event) {
            ScoutnetSyncEvent::sync($event, $this->calendar);
        });
    }

}
