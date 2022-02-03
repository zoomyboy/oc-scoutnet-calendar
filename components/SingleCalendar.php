<?php

namespace Zoomyboy\Scoutnet\Components;

use \Cms\Classes\ComponentBase;
use Carbon\Carbon;
use Input;
use October\Rain\Database\Collection;
use Response;
use Zoomyboy\Scoutnet\Classes\EventRepository;
use Zoomyboy\Scoutnet\Classes\ScoutnetSync;
use Zoomyboy\Scoutnet\Models\Calendar;
use Zoomyboy\Scoutnet\Models\Event;
use Zoomyboy\Scoutnet\Models\Keyword;
use Zoomyboy\Scoutnet\Models\Tag;

class SingleCalendar extends ComponentBase {

    public Collection $calendars;
    public Collection $tags;
    public Collection $events;
    public array $filter = [];

    public function componentDetails() {
        return [
            'name' => "Single",
            'description' => "Display a single Calendar"
        ];
    }

    public function onRun(): void
    {
        $this->page['bodyTag'] .= ' data-scoutnet ';
    }

    public function onRender() {
        $this->tags = Tag::select('title', 'id')->orderBy('title')->get();
        $this->calendars = Calendar::select('title', 'color', 'id')->orderBy('title')->get();
        $this->filter = [
            'calendars' => [$this->property('calendar_id')],
            'tags' => [],
            'showPast' => false
        ];
        $this->events = $this->events();
    }

    public function events(): Collection {
        return app(EventRepository::class)->forFrontend($this->filter)->group();
    }

    public function onFilter() {
        $this->filter = Input::all();

        return Response::make(
            $this->renderPartial($this->alias.'::events', ['events' => $this->events()]),
            200
        );
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

