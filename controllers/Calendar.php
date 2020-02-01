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
        $calendar = CalendarModel::findOrFail($recordId);
        $calendar->scoutnetSync()->sync();
    }

    public function formBeforeSave($model) {
        $model->connectionService('google_calendar')->storeCalendar(request()->input('Calendar._google_calendar'));
    }

    public function formExtendModel($model) {
        $model->setAttribute('_google_calendar', $model->connectionService('google_calendar')->currentCalendar());
        return $model;
    }

    public function onGetTitle() {
        try {
            $group = ScoutnetSync::fromGroup(Input::get('Calendar.scoutnet_id'), null);

            return response($group->getName());
        } catch(Exception $e) {
            return response('');
        }
    }

    public function callback($connection, ...$params) {
        $service = get_class($this->formCreateModelObject()->connectionService($connection));
        $record = $service::fromRedirectUri($params, url()->current());
        $record->connectionService($connection)->setLogin();

        return redirect()->to(Backend::url('zoomyboy/scoutnet/calendar/update/'.$record->id));
    }

    public function logout($connection, $recordId = null) {
        $model = $this->formFindModelObject($recordId);
        if (!$model) {
            throw new ApplicationException('Login fehlgeschlagen.');
        }

        $model->connectionService($connection)->logout();

        return redirect()->to(Backend::url('zoomyboy/scoutnet/calendar/update/'.$recordId));
    }
}
