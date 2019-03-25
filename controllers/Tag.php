<?php namespace Zoomyboy\Scoutnet\Controllers;

use BackendMenu;
use Backend\Classes\Controller;

/**
 * Tag Back-end Controller
 */
class Tag extends Controller
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

        BackendMenu::setContext('Zoomyboy.Scoutnet', 'scoutnet', 'tag');
    }
}
