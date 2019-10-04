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

    public function formExtendModel($model) {
        if ($this->formGetContext() === 'create') {
            $model->fill(['calendar_id' => str_replace('calendar-', '', Request::input('parent'))]);
        }

        return $model;
    }
}
