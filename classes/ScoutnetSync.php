<?php

namespace Zoomyboy\Scoutnet\Classes;

use Zoomyboy\Scoutnet\Models\Calendar;
use Zoomyboy\Scoutnet\Exceptions\CalendarNotFoundException;

class ScoutnetSync {
	private $calendarId;
	private $sn;

	public function __construct($calendarId) {
		try {
			$this->calendarId = $calendarId;
			require_once(plugins_path() . '/zoomyboy/scoutnet/vendor/scoutnet-api-client-php/src/scoutnet.php');
			$this->sn = scoutnet()->group($calendarId);
		} catch(\SN_Exception_EndUser $e) {
			if (str_contains($e->getMessage(), 'Es gibt kein group')) {
				throw new CalendarNotFoundException();
			}
		}

		return $this;
	}

	public static function fromGroup($calendarId) {
		return new static($calendarId);
	}

	public function getName() {
		return $this->sn->name;
	}

    public function isValid() {
        return $this->sn !== null;
    }

	public function getZip() {
		return $this->sn->zip;
	}

	public function getCity() {
		return $this->sn->city;
	}

	public function getId() {
		return $this->sn->global_id;
	}

	public function events() {
		return new ScoutnetSyncEvents($this->sn);
	}
}
