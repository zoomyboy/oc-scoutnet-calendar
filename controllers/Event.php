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
    use HasNestedList;

    public $implement = [
        'Backend.Behaviors.FormController',
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    public function index() {}

    public function onCreate() {
        $this->vars['mode'] = 'adding';
        parent::create();

        return [
            'env' => $this->getEnv($this->widget->form->model),
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
            'env' => $this->getEnv($this->widget->form->model),
            'content' => $this->makePartial('edit', [
                'form' => $this->widget->form,
            ])
        ];
    }

    public function formExtendModel($model) {
        if ($this->formGetContext() === 'create') {
            $model->fill(['calendar_id' => str_replace('calendar-', '', Request::input('parent'))]);
        }

        return $model;
    }

    public function create_onSave() {
        parent::create_onSave();

        return ['model' => $this->formGetModel(), 'env' => $this->getEnv()];
    }

    public function update_onSave($recordId = null, $context = null) {
        parent::update_onSave($recordId, $context);

        return ['model' => $this->formGetModel(), 'env' => $this->getEnv() ];
    }

    public function onDelete() {
        foreach(Input::get('events') as $event) {
            $this->update_onDelete($event);
        }
    }
}
