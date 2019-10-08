<?php namespace Zoomyboy\Scoutnet\FormWidgets;

use Input;
use Backend;
use Zoomyboy\Scoutnet\Plugin;
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

    /**
     * @inheritDoc
     */
    public function init()
    {

    }

    /**
     * @inheritDoc
     */
    public function render()
    {
        return $this->makePartial('connectbutton');
    }

    public function loadAssets()
    {
        $this->addCss('css/connectbutton.css', 'zoomyboy.scoutnet');
    }

    public function onGetLoginForm() {
        return $this->makePartial('login_form', [
            'id' => Input::get('formId'),
            'action' => Plugin::$loginUrl
        ]);
    }

    public function onGetLogoutForm() {
        preg_match('/[0-9]$/', request()->url(), $matches);
        $id = $matches[0];

        return $this->makePartial('logout_form', [
            'id' => Input::get('formId'),
            'action' => Backend::url('zoomyboy/scoutnet/calendar/logout/'.$id)
        ]);
    }

    public function getSaveValue($value)
    {
         return FormField::NO_SAVE_DATA;
    }
}
