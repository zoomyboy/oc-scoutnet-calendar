<?php

namespace Zoomyboy\Scoutnet\Behaviors;

use Lang;
use \Backend\Behaviors\FormController;
use Zoomyboy\Scoutnet\Widgets\CalendarList;

trait HasNested {
    public function initNestedPage() {
        $this->addJs('/modules/backend/assets/js/october.treeview.js', 'core');
        $this->addJs('/plugins/zoomyboy/scoutnet/assets/js/calendar-list.js');
        $this->addJs('/plugins/zoomyboy/scoutnet/assets/js/connectbutton.js');

        $this->controller->bodyClass = 'compact-container';

        $calendarList = new CalendarList($this->controller);
        $calendarList->alias = 'calendarList';
        $calendarList->bindToController();
    }
}

