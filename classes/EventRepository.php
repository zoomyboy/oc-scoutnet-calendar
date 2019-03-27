<?php

namespace Zoomyboy\Scoutnet\Classes;

use Carbon\Carbon;
use Zoomyboy\Scoutnet\Models\Event;

class EventRepository {

    public $months = ['', 'Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'];

    public function get($filter = []) {
        $minDate = empty($filter['showPast'])
            ? Carbon::now()
            : Carbon::now()->startOfYear()->subYears(1);

        $query = (new Event())->newQuery()
            ->with(['keywords'])
            ->select('title', 'organizer', 'starts_at', 'ends_at', 'location', 'target', 'id')
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

        return $query->get()->groupBy(function($e) {
            return $this->months[Carbon::parse($e->starts_at)->format('n')]
            .' '.Carbon::parse($e->starts_at)->format('Y');
        });
    }
}
