<?php

namespace Zoomyboy\Scoutnet\Widgets;

use Input;
use Backend\Classes\WidgetBase;
use Zoomyboy\Scoutnet\Models\Calendar;

class CalendarList extends WidgetBase {
    use \Backend\Traits\SearchableWidget;
    use \Backend\Traits\SelectableWidget;

    public $deleteConfirmation = 'rainlab.pages::lang.page.delete_confirmation';

    public function render() {
        return $this->makePartial('body', [
            'data' => $this->getData()
        ]);
    }

    public function getData() {
        if ($this->getSearchTerm()) {
            return Calendar::where('name', 'LIKE', '%'.$this->getSearchTerm().'%')->get();
        }

        return Calendar::get();
    }

    public function onSearch() {
        $this->setSearchTerm(Input::get('search'));
        $this->extendSelection();

        return $this->updateList();
    }

    public function updateList() {
        return [
            '#'.$this->getId('calendar-list') => $this->makePartial('items', [
                'items' => $this->getData()
            ])
        ];
    }

    public function onUpdate()
    {
        $this->extendSelection();

        return $this->updateList();
    }
}
