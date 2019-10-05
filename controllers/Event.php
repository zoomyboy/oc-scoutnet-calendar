<?php namespace Zoomyboy\Scoutnet\Controllers;

use BackendMenu;
use Backend\Classes\Controller;

/**
 * Calendar Back-end Controller
 */
class Event extends Controller
{
    public $implement = [
        'Zoomyboy.Scoutnet.Behaviors.NestedFormController',
        'Zoomyboy.Scoutnet.Behaviors.NestedListController'
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    public $parent = 'calendar';

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Zoomyboy.Scoutnet', 'scoutnet', 'calendar');
    }
}
