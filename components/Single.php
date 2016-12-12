<?php

namespace Zoomyboy\Scoutnetcalendar\Components;

use \Cms\Classes\ComponentBase;
use Zoomyboy\Scoutnetcalendar\Models\Calendar;
use Zoomyboy\Scoutnetcalendar\Classes\ScoutnetSync;

class Single extends ComponentBase {
	private $calendar = false;

	public function componentDetails() {
		return [
			'name' => "Single",
			'description' => "Display a single Calendar"
		];
	}

	public function getCalendarInstance() {
		if ($this->calendar !== false) {
			return $this->calendar;
		}
		if ($this->property('calendarId')) {
			$id = Calendar::find($this->property('calendarId'))->scoutnet_id;
			return ScoutnetSync::fromGroup($id);
		}
		return false;
	}
		

	public function calendar() {
		return $this->getCalendarInstance();
	}

	public function events() {
		$calendar = $this->getCalendarInstance();
		return $calendar->events()
			->ofYear($this->getYear())
			->theFirst($this->property('maxEntries'))
			->get();
	}

	public function onRun() {
		$this->page['calendar'] = $this->getCalendarInstance();
		$this->page['calendarYear'] = $this->getYear();
	}

	public function defineProperties() {
		return [
			'calendarId' => [
				'title' => 'Calendar',
				'description' => 'Select the calendar to display',
				'required' => true,
				'type' => 'dropdown',
				'placeholder' => 'Select...',
				'options' => Calendar::getSelectArray()
			],
			'year' => [
				'title' => 'Year',
				'description' => 'Select the year(s) for the events, optional divided by "|"',
				'type' => 'string'
			],
			'maxEvents' => [
				'title' => 'Max Events',
				'description' => 'Show not more than the given amount',
				'type' => 'string',
				'default' => '-1'
			]
		];
	}

	private function getYear() {
		$currentYear = date('Y');
		$prop = $this->property('year');

		if ($prop == '') {
			return $currentYear;
		}

		if (substr($prop, 0, 1) == '-') {
			return $currentYear - abs($prop);
		}
		if (substr($prop, 0, 1) == '+') {
			return $currentYear + abs($prop);
		}

		return $prop;
	}
}

