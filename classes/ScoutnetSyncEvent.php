<?php

namespace Zoomyboy\Scoutnet\Classes;

use Carbon\Carbon;
use Zoomyboy\Scoutnet\Models\Calendar;
use Zoomyboy\Scoutnet\Models\Event;
use Zoomyboy\Scoutnet\Models\Keyword;

class ScoutnetSyncEvent {

    private $event;
    private $calendar;

    public static function sync($event, $calendar) {
        $self = new static($event, $calendar);
        $self->handle();
    }

    private function __construct($event, $calendar) {
        $this->event = $event;
        $this->calendar = $calendar;
    }

    private function getStart() {
        if (!$this->event->start_date) { return null; }
        $start = $this->event->start_date.($this->event->start_time ? ' '.$this->event->start_time : ' 00:00:00');

        return Carbon::parse($start);
    }

    public function getEnd() {
        if ($this->event->end_date == '0000-00-00') { $this->event->end_date = false; }
        if (!$this->event->end_date && !$this->event->end_time) {
            // Event hat kene Endzeit und kein Enddatum. Es wird eine Stunde Dauer angenommen oder ein Tagesevent
            return $this->getStart()->format('H:i:s') == '00:00:00'
                ? $this->getStart()
                : $this->getStart()->addHours(1);
        }

        if ($this->event->end_date && !$this->event->end_time) {
            return Carbon::parse($this->event->end_date.' '.$this->getStart()->format('H:i:s'));
        }

        if (!$this->event->end_date && $this->event->end_time) {
            return Carbon::parse($this->getStart()->format('Y-m-d').' '.$this->event->end_time);
        }

        return Carbon::parse($this->event->end_date.' '.$this->event->end_time);
    }

    public function updateOrCreate($wheres, $attributes) {
        if (Event::where($wheres)->exists()) {
            return tap(Event::where($wheres)->first(), function($event) use ($attributes) {
                $event->update($attributes);
            });
        } else {
            return Event::create($attributes);
        }
    }

    private function handle() {
        $local = $this->updateOrCreate(['scoutnet_id' => $this->event->id], [
            'calendar_id' => $this->calendar->id,
            'title' => $this->event->title,
            'location' => $this->event->location && $this->event->location !== 'NULL'
                ? $this->event->location
                : null,
            'starts_at' => $this->getStart(),
            'ends_at' => $this->getEnd(),
            'organizer' => $this->event->organizer ?: null,
            'target' => $this->event->target_group ?: null,
            'url' => $this->event->url ?: null,
            'url_text' => $this->event->url_text ?: null,
            'description' => $this->event->description ?: null,
            'scoutnet_id' => $this->event->id
        ]);

        $keywords = collect([]);
        foreach($this->event->keywords as $keywordId => $keyword) {
            $keywords->push(Keyword::updateOrCreate(['scoutnet_id' => $keywordId], [
                'scoutnet_id' => $keywordId,
                'title' => $keyword
            ]));
        }
        $local->keywords()->sync($keywords->pluck('id')->toArray());
    }
}
