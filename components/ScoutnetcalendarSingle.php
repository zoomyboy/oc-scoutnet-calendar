<?php

namespace Zoomyboy\Scoutnetcalendar\Components;

use \Cms\Classes\ComponentBase;
use Zoomyboy\Scoutnetcalendar\Models\Calendar;
use Zoomyboy\Scoutnetcalendar\Classes\ScoutnetSync;
use Carbon\Carbon;

class ScoutnetcalendarSingle extends ComponentBase {
	private $calendar = false;
	public $calendarYear;
	public $yearList;

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
			->ofYears($this->getYears())
			->theFirst($this->property('maxEvents'))
			->get();
	}

	public function onRun() {
		$this->yearList = implode(', ', $this->getYears());
	}

	public function onRender() {
		$this->page['calendar'] = $this->getCalendarInstance();
		$this->page['calendarYear'] = $this->getYears();
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
			'years' => [
				'title' => 'Years',
				'description' => 'Enter the year(s) for the events, optional divided by "|"',
				'type' => 'string'
			],
			'relative' => [
				'title' => 'Relative Years',
				'description' => 'Are the years relative to the current (e.g. 0 would be the current year)',
				'type' => 'checkbox'
			],
			'maxEvents' => [
				'title' => 'Max Events',
				'description' => 'Show not more than the given amount',
				'type' => 'string',
				'default' => '-1'
			]
		];
	}

	private function getYears() {
		if (!trim($this->property('years'))) {
			return [];
		}

		if ($this->property('relative')) {
			return array_unique(array_map(function($year) {
				$year = trim($year);
				$first = substr($year, 0, 1);
				
				if (is_numeric($first)) {
					return Carbon::now()->addYears($year)->format('Y');
				}

				if ($first == '+') {
					return Carbon::now()->addYears(substr($year, 1))->format('Y');
				}

				if ($first == '-') {
					return Carbon::now()->subYears(substr($year, 1))->format('Y');
				}
			}, explode('|', $this->property('years'))));
		} else {
			return array_filter(explode('|', $this->property('years')), function($year) {
				return is_numeric($year) && is_numeric(substr($year, 0, 1)) && $year > 0;
			});
		}

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

