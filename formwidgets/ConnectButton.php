<?php namespace Zoomyboy\Scoutnet\FormWidgets;

use Input;
use Backend;
use Backend\Classes\FormWidgetBase;
use Zoomyboy\Scoutnet\Models\Setting;
use Backend\Classes\FormField;

/**
 * ConnectButton Form Widget
 */
class ConnectButton extends FormWidgetBase
{
    /**
     * @inheritDoc
     */
    protected $defaultAlias = 'zoomyboy_scoutnet_connect_button';
    public $connection;
    public $service;
    public $btnClass;

    /**
     * @inheritDoc
     */
    public function init()
    {
        $this->fillFromConfig(['connection']);
    }

    public function getService() {
        $this->service = $this->model->connectionService($this->connection);
    }

    /**
     * @inheritDoc
     */
    public function render()
    {
        $this->getService();

        $this->btnClass = 'btn-'.$this->connection;
        if ($this->service->isConnected()) {
            $this->btnClass .= ' '.$this->btnClass.'-connected';
        }

        return $this->makePartial('connectbutton');
    }

    public function loadAssets()
    {
        $this->addCss('css/connectbutton.css', 'zoomyboy.scoutnet');
    }

    public function onGetLoginForm() {
        $this->getService();

        return $this->makePartial($this->connection.'_login_form', [
            'id' => Input::get('formId'),
            'action' => $this->service->getAuthUrl(),
            'params' => $this->service->getAuthParams()
        ]);
    }

    public function onGetLogoutForm() {
        preg_match('/[0-9]$/', request()->url(), $matches);
        $id = $matches[0];

        return $this->makePartial('logout_form', [
            'id' => Input::get('formId'),
            'action' => Backend::url('zoomyboy/scoutnet/calendar/logout/'.$this->connection.'/'.$id)
        ]);
    }

    public function getSaveValue($value)
    {
         return FormField::NO_SAVE_DATA;
    }
}
