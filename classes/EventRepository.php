<?php

namespace Zoomyboy\Scoutnet\Classes;

use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Zoomyboy\Scoutnet\Models\Event;

class EventRepository
{
    public $query;

    /**
     * @var array<int, callable(Builder<Event>): void>
     */
    public static array $queryCallbacks = [];

    /**
     * @param array<int, string>
     */
    public array $months = ['', 'Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'];

    public function forFrontend($filter = [])
    {
        $minDate = data_get($filter, 'showPast')
            ? Carbon::now()->startOfYear()->subYears(1)
            : Carbon::now();

        $query = (new Event())->newQuery()
            ->with(['keywords'])
            ->select('title', 'calendar_id', 'organizer', 'starts_at', 'ends_at', 'location', 'target', 'id', 'url')
            ->selectRaw('(SELECT GROUP_CONCAT(zoomyboy_scoutnet_keywords.title SEPARATOR \', \') from zoomyboy_scoutnet_keywords WHERE zoomyboy_scoutnet_keywords.id IN (SELECT zoomyboy_scoutnet_event_keyword.keyword_id FROM zoomyboy_scoutnet_event_keyword WHERE zoomyboy_scoutnet_event_keyword.event_id=zoomyboy_scoutnet_events.id)) AS keywordList')
            ->selectRaw('(SELECT color from zoomyboy_scoutnet_calendars WHERE id=zoomyboy_scoutnet_events.calendar_id) AS color')
            ->selectRaw('(SELECT title from zoomyboy_scoutnet_calendars WHERE id=zoomyboy_scoutnet_events.calendar_id) AS calendar_name')
            ->where('starts_at', '>=', $minDate)
            ->orderBy('starts_at');

        if (!empty($filter['calendars'])) {
            $query->whereIn('calendar_id', $filter['calendars']);
        }

        if (!empty($filter['tags']) || !empty($filter['keywords'])) {
            $query->whereHas('keywords', function ($k) use ($filter) {
                if (!empty($filter['tags'])) {
                    $k->whereHas('tags', function ($t) use ($filter) {
                        return $t->whereIn('id', $filter['tags']);
                    });
                }
                if (!empty($filter['keywords'])) {
                    $k->whereIn('id', $filter['keywords']);
                }
            });
        }

        static::runExtensions($query);

        $this->query = $query;

        return $this;
    }

    // @todo get this as a column from mySQL
    public function group()
    {
        return $this->query->get()->groupBy(function ($e) {
            return $this->months[Carbon::parse($e->starts_at)->format('n')]
            .' '.Carbon::parse($e->starts_at)->format('Y');
        });
    }

    public function forIcal($filters = [])
    {
        $this->forFrontend($filters);
        $this->query->addSelect('description');

        return $this;
    }

    public function get()
    {
        return $this->query->get();
    }

    /**
     * @param callable(Builder<Event> $query): void
     */
    public static function extendQuery(callable $query): void
    {
        static::$queryCallbacks[] = $query;
    }

    /**
     * @param Builder<Event> $query
     */
    public static function runExtensions($query): void
    {
        foreach (static::$queryCallbacks as $queryCallback) {
            $queryCallback($query);
        }
    }
}
