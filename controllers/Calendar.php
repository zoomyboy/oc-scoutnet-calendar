<?php namespace Zoomyboy\Scoutnet\Controllers;

use BackendMenu;
use Lang;
use Input;
use Backend\Classes\Controller;
use Zoomyboy\Scoutnet\Models\Calendar as CalendarModel;
use Zoomyboy\Scoutnet\Classes\ScoutnetSync;
use Zoomyboy\Scoutnet\Widgets\CalendarList;

/**
 * Calendar Back-end Controller
 */
class Calendar extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController'
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    public function __construct()
    {
        parent::__construct();

        $calendarList = new CalendarList($this);
        $calendarList->alias = 'calendarList';
        $calendarList->bindToController();

        BackendMenu::setContext('Zoomyboy.Scoutnet', 'scoutnet', 'calendar');
    }

    public function index() {
        $this->addJs('/modules/backend/assets/js/october.treeview.js', 'core');
        $this->addJs('/plugins/zoomyboy/scoutnet/assets/js/calendar-list.js');

        $this->bodyClass = 'compact-container';

        $this->pageTitle = Lang::get($this->getConfig(
            'title',
            'backend::lang.list.default_title'
        ));
        $this->makeLists();
    }

    public function onDeleteObjects() {
        $indexes = collect(Input::get('object'))
            ->filter(function($o) {
                return $o == 1;
            })
            ->keys();

        $ids = $indexes->map(function($o) {
            return intVal(str_replace('s', '', $o));
        })
        ->toArray();

        CalendarModel::whereIn('id', $ids)->delete();

        return [
            'deleted' => $indexes->toArray(),
            'error'   => null
        ];
    }

    public function onCreateObject() {
        $this->create();

        $this->vars['mode'] = 'adding';

        $result = [
            'tabTitle' => 'Neuer Kalender',
            'tab'      => $this->formRender()
        ];

        return $result;
    }
}
