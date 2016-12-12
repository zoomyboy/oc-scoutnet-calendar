<?php namespace Zoomyboy\Scoutnetcalendar\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use Zoomyboy\Scoutnetcalendar\Classes\ScoutnetSync;

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
		
        BackendMenu::setContext('Zoomyboy.Scoutnetcalendar', 'scoutnetcalendar', 'calendar');
    }
}
