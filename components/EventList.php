<?php

namespace Zoomyboy\Scoutnet\Components;

use Cms\Classes\ComponentBase;
use Illuminate\Support\Collection;
use Zoomyboy\Scoutnet\Classes\EventRepository;
use Zoomyboy\Scoutnet\Models\Calendar;
use Zoomyboy\Scoutnet\Models\Event;

/**
 * EventList Component.
 */
class EventList extends ComponentBase
{
    public Collection $events;

    public function componentDetails()
    {
        return [
            'name' => 'EventList',
            'description' => 'Zeigt eine Liste von Terminen an',
        ];
    }

    public function defineProperties()
    {
        return [
            'calendar' => [
                'type' => 'dropdown',
                'title' => 'Kalender',
                'required' => true,
            ],
        ];
    }

    public function onRender(): void
    {
        $query = Event::select('*')->where('calendar_id', $this->property('calendar'))
            ->orderBy('starts_at')
            ->where('starts_at', '>=', now())
            ->limit(5);

        EventRepository::runExtensions($query);

        $this->events = $query->get();
    }

    public function getCalendarOptions()
    {
        return Calendar::forSelect();
    }
}
