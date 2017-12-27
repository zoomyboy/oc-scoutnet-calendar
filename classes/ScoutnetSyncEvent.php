<?php

namespace Zoomyboy\Scoutnetcalendar\Classes;

use Carbon\Carbon;

class ScoutnetSyncEvent {
	private $event;

	private $shortFormat = '%d %b %y';

	private $props = [
		'title', 'whenShort', 'location'
	];

	public function __construct($event) {
		$this->event = $event;
	}

	public function getTitleAttribute() {
		return $this->event->title;
	}

	/**
	 * Localization of month string is broken due to an issue in
	 * october. The Carbon package doesnt recognize the app.locale
	 * setting, so we set this manually and doesnt use carbon at all...
	 */
	public function getWhenShortAttribute() {
		setlocale(LC_TIME, 'de_DE.UTF-8');

		$string = strftime($this->shortFormat, Carbon::parse($this->event->start_date)->timestamp);

		if (!$this->isOnlyOneDay()) {
			$string .= ' - '.strftime($this->shortFormat, Carbon::parse($this->event->end_date)->timestamp);
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
		return is_null($this->event->end_date) 
			|| Carbon::parse($this->event->start_date)
			->eq(Carbon::parse($this->event->end_date));
	}
}
