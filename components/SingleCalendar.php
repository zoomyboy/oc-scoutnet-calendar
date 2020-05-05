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
use RainLab\Pages\Interfaces\Gutenbergable;

class SingleCalendar extends ComponentBase implements Gutenbergable {
    private $calendar = false;
    public $calendarYear;
    public $yearList;
    public $groupBy;
    public $events;
    public $columns;

    /**
     * @todo configure filter
     */
    public function defaultFilter() {
        return [
            'calendars' => [1],
            'categories' => [],
            'showPast' => false
        ];
    }

    public function defineProperties()
    {
        return [
            'category' => [
                'type' => 'dropdown',
                'label' => 'Kategorie',
                'required' => true,
            ],
            'layout' => [
                'default' => 'default.htm',
                'label' => 'Layout',
                'type' => 'dropdown',
            ],
            'columns' => [
                'default' => '1',
                'label' => 'Spalten',
                'type' => 'dropdown',
            ],
            'activeCalendar' => [
                'type' => 'dropdown',
                'required' => true,
                'label' => 'zoomyboy.scoutnet::lang.activeCalendar'
            ],
            'groupBy' => [
                'type' => 'dropdown',
                'label' => 'zoomyboy.scoutnet::lang.groupByLabel',
                'emptyOption' => 'zoomyboy.scoutnet::lang.groupBy.nothing',
            ]
        ];
    }

    public function getColumnsOptions() {
        return [ '1' => 1, '2' => 2, '3' => 3];
    }

    public function getGroupByOptions() {
        return [
            'year' => 'zoomyboy.scoutnet::lang.groupBy.year',
            'month' => 'zoomyboy.scoutnet::lang.groupBy.month',
        ];
    }

    public function getLayoutOptions() {
        return [
            'def.htm' => 'Standard',
            'blocks.htm' => 'Kacheln'
        ];
    }


    public function componentDetails() {
        return [
            'name' => "Scoutnet-Termine",
            'description' => "Display a single Calendar",
            'icon' => 'calendar',
        ];
    }

    public function getEvents($filter = []) {
        $filter = array_merge($this->defaultFilter(), $filter);
        return app('scoutnet.events')->forFrontend($filter)->group($this->groupBy);
    }

    public function onRun() {
        $this->page['calendars'] = Calendar::orderBy('title')->get();
        $this->page['categories'] = Tag::orderBy('title')->get();
        $this->groupBy = $this->property('groupBy', null);
        $this->events = $this->getEvents();
        $this->page['filter'] = $this->defaultFilter();
        $this->page['href'] = $this->generateExportLink();
        $this->columns = $this->property('columns', 1);
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

    public function getActiveCalendarOptions() {
        return Calendar::get()->pluck('title', 'id')->toArray();
    }

    public function getCategoryOptions() {
        return Tag::get()->pluck('title', 'id')->toArray();
    }
}

