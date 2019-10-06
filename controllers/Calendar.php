<?php namespace Zoomyboy\Scoutnet\Controllers;

use Input;
use Backend;
use Request;
use Exception;
use BackendMenu;
use ApplicationException;
use Backend\Classes\Controller;
use Zoomyboy\Scoutnet\Classes\ScoutnetSync;
use Zoomyboy\Scoutnet\Models\Calendar as CalendarModel;

/**
 * Calendar Back-end Controller
 */
class Calendar extends Controller
{
    public $implement = [
        'Zoomyboy.Scoutnet.Behaviors.NestedFormController',
        'Zoomyboy.Scoutnet.Behaviors.NestedListController'
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Zoomyboy.Scoutnet', 'scoutnet', 'calendar');
    }

    public function update_onSync($recordId = null) {
        CalendarModel::findOrFail($recordId)->pullEvents();
    }

    public function onGetTitle() {
        try {
            $group = ScoutnetSync::fromGroup(Input::get('Calendar.scoutnet_id'));

            return response($group->getName());
        } catch(Exception $e) {
            return response('');
        }
    }

    public function callback($recordId = null) {
        $model = $this->formFindModelObject($recordId);

        if (!$model || !Request::filled('auth') || Request::input('logintype') != 'login') {
            throw new ApplicationException('Login fehlgeschlagen.');
        }

        $model->setLogin(Request::get('auth'));

        return redirect()->to(Backend::url('zoomyboy/scoutnet/calendar/update/'.$recordId));
    }
}
