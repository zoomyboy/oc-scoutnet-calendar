<?php

namespace Zoomyboy\Scoutnet\Classes;

use Carbon\Carbon;
use Zoomyboy\Scoutnet\Models\Event;

class EventRepository {

    public $query;

    public $months = ['', 'Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'];

    public function forFrontend($filter = []) {
        $minDate = empty($filter['showPast'])
            ? Carbon::now()
            : Carbon::now()->startOfYear()->subYears(1);

        $query = (new Event())->newQuery()
            ->with(['keywords'])
            ->select('title', 'description', 'calendar_id', 'organizer', 'starts_at', 'ends_at', 'location', 'target', 'id', 'url', 'url_text')
            ->selectSub('
            IF(DATE_FORMAT(starts_at, "%Y%m%d") = DATE_FORMAT(ends_at, "%Y%m%d"), DATE_FORMAT(starts_at, "%d. %b"),
            IF(DATE_FORMAT(starts_at, "%Y%m") = DATE_FORMAT(ends_at, "%Y%m"), CONCAT(DATE_FORMAT(starts_at, "%d."), " - ", DATE_FORMAT(ends_at, "%d. %b")),
            IF(DATE_FORMAT(starts_at, "%Y") = DATE_FORMAT(ends_at, "%Y"), CONCAT(DATE_FORMAT(starts_at, "%d. %b"), " - ", DATE_FORMAT(ends_at, "%d. %b")),
            NULL
            )))', 'date')
            ->selectSub('SUBSTRING(title, LOCATE(" - ", title))', 'subtitle')
            ->selectSub('IF(LOCATE(" - ", title) != 0, SUBSTRING(title, LOCATE(" - ", title)+2), "")', 'subtitle')
            ->selectSub('IF(LOCATE(" - ", title) != 0, SUBSTRING(title, 1, LOCATE(" - ", title)), title)', 'maintitle')
            ->selectRaw('(SELECT GROUP_CONCAT(zoomyboy_scoutnet_keywords.title SEPARATOR \', \') from zoomyboy_scoutnet_keywords WHERE zoomyboy_scoutnet_keywords.id IN (SELECT zoomyboy_scoutnet_event_keyword.keyword_id FROM zoomyboy_scoutnet_event_keyword WHERE zoomyboy_scoutnet_event_keyword.event_id=zoomyboy_scoutnet_events.id)) AS keywordList')
            ->where('starts_at', '>=', $minDate)
            ->orderBy('starts_at');

        if (!empty($filter['calendars'])) {
            $query->whereIn('calendar_id', $filter['calendars']);
        }

        if (!empty($filter['categories'])) {
            $query->whereHas('keywords', function($k) use ($filter) {
                return $k->whereHas('tags', function($t) use ($filter) {
                    return $t->whereIn('id', $filter['categories']);
                });
            });
        }

        $this->query = $query;

        return $this;
    }

    // @todo get this as a column from mySQL
    public function group() {
        return $this->query->get()->groupBy(function($e) {
            return $this->months[Carbon::parse($e->starts_at)->format('n')]
            .' '.Carbon::parse($e->starts_at)->format('Y');
        });
    }

    public function forIcal($filters = []) {
        $this->forFrontend($filters);
        $this->query->addSelect('description');

        return $this;
    }

    public function get() {
        return $this->query->get();
    }
}
