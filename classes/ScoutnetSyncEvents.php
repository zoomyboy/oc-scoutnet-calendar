<?php

namespace Zoomyboy\Scoutnetcalendar\Classes;

use Carbon\Carbon;

class ScoutnetSyncEvents {
	private $sn;
	private $startMin;
	private $startMax;
	private $onlyFirst = -1;
	private $onlyLast = -1;

	public function __construct($scoutnet) {
		$this->sn = $scoutnet;
	}
	
	public function ofCurrentYear() {
		$this->startMin = Carbon::now()->startOfYear()->format('Y-m-d');
		$this->startMax = Carbon::now()->endOfYear()->format('Y-m-d');

		return $this;
	}

	public function ofYear($year) {
		$this->startMin = Carbon::createFromDate($year, 1,1)->startOfYear()->format('Y-m-d');
		$this->startMax = Carbon::createFromDate($year, 1,1)->endOfYear()->format('Y-m-d');

		return $this;
	}

	public function onlyFuture() {

	}

	public function onlyPast() {

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

		if ($this->startMin) {
			$query[] = "start_date >= '".$this->startMin."'";
		}

		if ($this->startMax) {
			$query[] = "start_date <= '".$this->startMax."'";
		}

		return implode (' AND ', $query);
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

