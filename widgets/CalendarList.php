<?php

namespace Zoomyboy\Scoutnet\Widgets;

use Input;
use Backend\Classes\WidgetBase;
use Zoomyboy\Scoutnet\Models\Calendar;
use \Backend\Traits\SearchableWidget;
use \Backend\Traits\SelectableWidget;

class CalendarList extends WidgetBase {
    use SearchableWidget;
    use SelectableWidget;

    public $deleteConfirmation = 'rainlab.pages::lang.page.delete_confirmation';

    public function render() {
        $this->vars['modelType'] = null;
        $this->vars['modelId'] = null;

        return $this->makePartial('body', [
            'data' => $this->getData()
        ]);
    }

    public function getData() {
        $query = Calendar::with(['events' => function($e) {
            return $e->orderBy('starts_at', 'DESC');
        }]);

        if ($this->getSearchTerm()) {
            $query->where('name', 'LIKE', '%'.$this->getSearchTerm().'%');
        }

        return $query->get();
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
        $this->vars['modelType'] = Input::get('modelType');
        $this->vars['modelId'] = Input::get('modelId');

        return $this->updateList();
    }

    /**
     * Reorder calendar items
     */
    public function onReorder()
    {
        $structure = collect(json_decode(Input::get('structure'), true))
            ->keys()
            ->map(function($calendar) {
                return str_replace('calendar-', '', $calendar);
            });

        $calendar = new Calendar();
        $calendar->setSortableOrder(
            $structure->toArray(),
            range(1, $calendar->newQuery()->get()->count())
        );
    }
}
