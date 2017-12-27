<?php

namespace Zoomyboy\Scoutnetcalendar\Classes;

use Carbon\Carbon;

class ScoutnetSyncEvents {
	private $sn;
	private $dates = [];
	private $onlyFirst = -1;
	private $onlyLast = -1;
	private $isFuture;

	public function __construct($scoutnet) {
		$this->sn = $scoutnet;
	}
	
	public function ofCurrentYear() {
		$this->ofYears([date('Y')]);

		return $this;
	}

	public function ofYears($years) {
		$dates = array_map(function($year) {
			return [
				Carbon::createFromDate($year, 1,1)->startOfYear()->format('Y-m-d'),
				Carbon::createFromDate($year, 1,1)->endOfYear()->format('Y-m-d')
			];
		}, $years);

		$this->dates = array_merge($this->dates, $dates);

		return $this;
	}

	public function onlyFuture() {
		$this->isFuture = true;

		return $this;
	}

	public function theFirst($first) {
		$this->onlyFirst = $first;

		return $this;
	}

	public function theLast($last) {
		$this->onlyLast = $last;

		return $this;
	}

	private function buildQuery() {
		$query = [];

		foreach ($this->dates as $range) {
			$thisQuery = [];

			if ($range[0] !== false) {
				$thisQuery[] = "start_date >= '".$range[0]."'";
			}
			if ($range[1] !== false) {
				$thisQuery[] = "start_date <= '".$range[1]."'";
			}

			$query[] = implode(' AND ', $thisQuery);
		}

		return 
			(count($query) ? '('.implode (' OR ', $query).')' : '')
			.($this->isFuture ? " AND start_date >= '".date('Y-m-d')."'" : '');
	}

	private function applyFirstAndLast($events) {
		if ($this->onlyLast == -1 && $this->onlyFirst >= 0) {
			return array_slice($events, 0, $this->onlyFirst);
		}
		if ($this->onlyLast >= 0 && $this->onlyFirst == -1) {
			return array_slice($events, $this->onlyLast * -1);
		}

		return $events;
	}

	public function get() {
		$return = [];
		$events = $this->sn->events($this->buildQuery());

		foreach($events as $event) {
			$return[] = new ScoutnetSyncEvent($event);
		}

		$return = $this->applyFirstAndLast($return);

		return $return;
	}
}

