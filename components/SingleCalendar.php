<?php

namespace Zoomyboy\Scoutnet\Components;

use Input;
use Carbon\Carbon;
use \Cms\Classes\ComponentBase;
use Zoomyboy\Scoutnet\Models\Tag;
use Zoomyboy\Scoutnet\Models\Event;
use Zoomyboy\Scoutnet\Models\Keyword;
use Zoomyboy\Scoutnet\Models\Calendar;
use Zoomyboy\Scoutnet\Classes\ScoutnetSync;

class SingleCalendar extends ComponentBase {
    private $calendar = false;
    public $calendarYear;
    public $yearList;
    public $months = ['', 'Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'];

    public function defaultFilter() {
        return [
            'calendars' => [3],
            'categories' => []
        ];
    }

    public function componentDetails() {
        return [
            'name' => "Single",
            'description' => "Display a single Calendar"
        ];
    }

    public function events($filter = null) {
        $filter = $filter ?: $this->defaultFilter();

        $query = (new Event())->newQuery()
            ->with(['keywords'])
            ->select('title', 'organizer', 'starts_at', 'ends_at', 'location', 'target', 'id')
            ->selectRaw('(SELECT GROUP_CONCAT(zoomyboy_scoutnet_keywords.title SEPARATOR \', \') from zoomyboy_scoutnet_keywords WHERE zoomyboy_scoutnet_keywords.id IN (SELECT zoomyboy_scoutnet_event_keyword.keyword_id FROM zoomyboy_scoutnet_event_keyword WHERE zoomyboy_scoutnet_event_keyword.event_id=zoomyboy_scoutnet_events.id)) AS keywordList')
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

    public function onRun() {
        $this->page['calendars'] = Calendar::orderBy('name')->get();
        $this->page['categories'] = Tag::orderBy('title')->get();
        $this->page['events'] = $this->events();
        $this->page['filter'] = $this->defaultFilter();
    }

    public function onFilter() {
        return [
            $this->alias.'::events' => $this->renderPartial($this->alias.'::events', [
                'events' => $this->events(Input::get('filter'))
            ])
        ];
    }

    public function defineProperties() {
        return [];
    }
}

