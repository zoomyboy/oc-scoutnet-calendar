<?php

namespace Zoomyboy\Scoutnet\Classes;

use Liliumdev\ICalendar\ZCiCal;
use Liliumdev\ICalendar\ZCiCalNode;
use Liliumdev\ICalendar\ZCiCalDataNode;

class IcalGenerator {
    public function output($filters) {
        $icalobj = new ZCiCal();

        foreach(app('scoutnetevents')->forIcal($filters)->get() as $event) {
            $event_end = "2020-01-01 13:00:00";

            $eventobj = new ZCiCalNode("VEVENT", $icalobj->curnode);

            $eventobj->addNode(new ZCiCalDataNode("SUMMARY:" . $event->title));
            $eventobj->addNode(new ZCiCalDataNode(
                "DTSTART:" . ZCiCal::fromSqlDateTime($event->icalStart))
            );

            // add end date
            $eventobj->addNode(new ZCiCalDataNode(
                "DTEND:" . ZCiCal::fromSqlDateTime($event->icalEnd))
            );

            $uid = 'scoutnet'.$event->id;
            $eventobj->addNode(new ZCiCalDataNode("UID:" . $uid));
            $eventobj->addNode(new ZCiCalDataNode("CATEGORIES:" . $event->keywordList));

            $eventobj->addNode(new ZCiCalDataNode("DTSTAMP:" . ZCiCal::fromSqlDateTime()));

            $eventobj->addNode(new ZCiCalDataNode("DESCRIPTION:" . $event->description));
            $eventobj->addNode(new ZCiCalDataNode("LOCATION:" . $event->location));

            if ($event->url) {
                $eventobj->addNode(new ZCiCalDataNode("URL:" . $event->url));
            }
        }

        echo $icalobj->export();
    }
}
