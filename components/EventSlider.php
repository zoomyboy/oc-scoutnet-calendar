<?php

namespace Zoomyboy\Scoutnet\Components;

use Cms\Classes\ComponentBase;
use Illuminate\Support\Collection;
use Zoomyboy\Scoutnet\Classes\EventRepository;
use Zoomyboy\Scoutnet\Models\Calendar;

/**
 * EventSlider Component.
 */
class EventSlider extends ComponentBase
{
    public Collection $events;

    public function componentDetails()
    {
        return [
            'name' => 'EventSlider Component',
            'description' => 'No description provided yet...',
        ];
    }

    public function onRender(): void
    {
        $this->events = app(EventRepository::class)->forFrontend([
            'calendars' => [$this->property('calendar')],
            'tags' => [],
            'keywords' => [$this->property('keyword')],
            'showPast' => false,
        ])->get();
    }

    public function getCalendarOptions(): array
    {
        return Calendar::forSelect();
    }

    public function defineProperties()
    {
        return [
            'calendar' => [
                'label' => 'Kalender',
                'type' => 'dropdown',
            ],
            'keyword' => [
                'label' => 'Keyword',
            ],
        ];
    }
}
