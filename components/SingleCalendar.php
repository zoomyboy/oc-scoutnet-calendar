<?php

namespace Zoomyboy\Scoutnet\Components;

use \Cms\Classes\ComponentBase;
use Carbon\Carbon;
use Input;
use October\Rain\Database\Collection;
use Zoomyboy\Scoutnet\Classes\ScoutnetSync;
use Zoomyboy\Scoutnet\Models\Calendar;
use Zoomyboy\Scoutnet\Models\Event;
use Zoomyboy\Scoutnet\Models\Keyword;
use Zoomyboy\Scoutnet\Models\Tag;

class SingleCalendar extends ComponentBase {

    public array $calendars;
    public array $categories;
    public Collection $events;
    public array $filter;
    public string $href;

    public function componentDetails() {
        return [
            'name' => "Single",
            'description' => "Display a single Calendar"
        ];
    }

    public function events(): Collection {
        return app('scoutnet.events')->forFrontend($this->filter)->group();
    }

    public function onRender() {
        $this->categories = Tag::orderBy('title')->get()->toArray();
        $this->filter = [
            'calendars' => [$this->property('calendar_id')],
            'categories' => $this->categories,
            'showPast' => false
        ];
        $this->calendars = Calendar::orderBy('title')->get()->toArray();
        $this->href = $this->generateExportLink();
        $this->events = $this->events();
    }

    public function generateExportLink() {
        return '/scoutnet-export/ical/calendar.ical?'.http_build_query(['filter' => $this->filter]);
    }

    public function onFilter() {
        $this->filter = Input::get('filter');
        return [
            $this->alias.'::events' => $this->renderPartial($this->alias.'::events', [
                'events' => $this->events()
            ]),
            $this->alias.'::export-button' => $this->renderPartial($this->alias.'::export-button', [
                'href' => $this->generateExportLink()
            ])
        ];
    }

    public function defineProperties() {
        return [
            'calendar_id' => [
                'label' => 'Kalender',
            ]
        ];
    }

    public function getCalendarIdOptions(): array
    {
        return static::staticGetCalendarOptions();
    }

    public static function staticGetCalendarOptions(): array
    {
        return Calendar::pluck('title', 'id')->toArray();
    }
}

