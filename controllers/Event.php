<?php namespace Zoomyboy\Scoutnet\Controllers;

use Lang;
use Input;
use Request;
use Backend\Classes\Controller;
use Zoomyboy\Scoutnet\Models\Event as EventModel;

/**
 * Calendar Back-end Controller
 */
class Event extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController'
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    public function onCreate() {
        $calendar = Request::input('calendar');
        $this->vars['calendar'] = $calendar;

        $this->vars['mode'] = 'adding';
        parent::create();

        return [
            'tabTitle' => Lang::get('zoomyboy.scoutnet::lang.newEvent'),
            'content' => $this->makePartial('create', [
                'form' => $this->widget->form,
            ])
        ];
    }

    public function onEdit() {
        $event = EventModel::findOrFail(Request::input('event'));

        $this->vars['mode'] = 'editing';
        parent::update($event->id);

        return [
            'tabTitle' => $event->title,
            'content' => $this->makePartial('edit', [
                'form' => $this->widget->form,
            ])
        ];
    }

    public function formExtendModel($model) {
        if ($this->formGetContext() === 'create') {
            $model->fill(['calendar_id' => Request::input('calendar')]);
        }

        return $model;
    }

    public function onStore() {
        parent::create_onSave();

        return ['model' => $this->formGetModel(), 'tabTitle' => $this->formGetModel()->title];
    }

    public function onUpdate() {
        $event = EventModel::findOrFail($this->params[0]);
        parent::update_onSave($event->id);

        return ['model' => $this->formGetModel(), 'tabTitle' => $this->formGetModel()->title];
    }

    public function onDelete() {
        foreach(Input::get('events') as $event) {
            $this->update_onDelete($event);
        }
    }
}
