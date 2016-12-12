<?php

namespace Zoomyboy\Scoutnetcalendar\Classes;

use Carbon\Carbon;

class ScoutnetSyncEvent {
	private $event;

	private $shortFormat = 'd M';

	private $props = [
		'title', 'whenShort', 'location'
	];

	public function __construct($event) {
		$this->event = $event;
	}

	public function getTitleAttribute() {
		return $this->event->title;
	}

	public function getWhenShortAttribute() {
		$string = Carbon::parse($this->event->start_date)->format($this->shortFormat);
		if (!$this->isOnlyOneDay()) {
			$string .= ' - '.Carbon::parse($this->event->end_date)->format($this->shortFormat);
		}

		return $string;
	}

	public function __get($var) {
		$method = 'get'.ucfirst($var).'Attribute';
		return $this->{$method}();
	}

	public function __isset($var) {
		return in_array ($var, $this->props);
	}

	public function getLocationAttribute() {
		return $this->event->location;
	}

	private function isOnlyOneDay() {
		return Carbon::parse($this->event->start_date)
			->eq(Carbon::parse($this->event->end_date));
	}
}
