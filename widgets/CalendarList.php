<?php

namespace Zoomyboy\Scoutnet\Widgets;

use Model;
use Input;
use Backend\Classes\WidgetBase;
use Zoomyboy\Scoutnet\Models\Calendar;
use \Backend\Traits\SearchableWidget;
use \Backend\Traits\SelectableWidget;

class CalendarList extends WidgetBase {
    use SearchableWidget;
    use SelectableWidget;

    public $activeClass = null;

    public $deleteConfirmation = 'rainlab.pages::lang.page.delete_confirmation';

    public function __construct($controller, $active = null) {
        parent::__construct($controller);

        if (is_a($active, Model::class)) {
            $this->setActive($active);
        }
    }

    public function render() {
        return $this->makePartial('body', [
            'data' => $this->getData()
        ]);
    }

    public function setActive(Model $model) {
        $this->activeClass = strtolower(class_basename($model)).'-'.$model->id;
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
        $this->activeClass = Input::get('modelType').'-'.Input::get('modelId');

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
