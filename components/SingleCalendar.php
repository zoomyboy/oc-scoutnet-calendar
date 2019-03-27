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

    /**
     * @todo configure filter
     */
    public function defaultFilter() {
        return [
            'calendars' => [3],
            'categories' => [],
            'showPast' => false
        ];
    }

    public function componentDetails() {
        return [
            'name' => "Single",
            'description' => "Display a single Calendar"
        ];
    }

    public function events($filter = []) {
        $filter = array_merge($this->defaultFilter(), $filter);
        return app('scoutnetevents')->forFrontend($filter)->group();
    }

    public function onRun() {
        $this->page['calendars'] = Calendar::orderBy('name')->get();
        $this->page['categories'] = Tag::orderBy('title')->get();
        $this->page['events'] = $this->events();
        $this->page['filter'] = $this->defaultFilter();
        $this->page['href'] = $this->generateExportLink();
    }

    public function generateExportLink($filter = []) {
        $filter = array_merge($this->defaultFilter(), $filter);
        return '/scoutnet-export/ical/calendar.ical?'.http_build_query(['filter' => $filter]);
    }

    public function onFilter() {
        return [
            $this->alias.'::events' => $this->renderPartial($this->alias.'::events', [
                'events' => $this->events(Input::get('filter'))
            ]),
            $this->alias.'::export-button' => $this->renderPartial($this->alias.'::export-button', [
                'href' => $this->generateExportLink(Input::get('filter'))
            ])
        ];
    }

    public function defineProperties() {
        return [];
    }
}

