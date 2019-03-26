<?php namespace Zoomyboy\Scoutnet\Controllers;

use BackendMenu;
use Backend\Classes\Controller;

/**
 * Keyword Back-end Controller
 */
class Keyword extends Controller
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

        BackendMenu::setContext('Zoomyboy.Scoutnet', 'scoutnet', 'keyword');
    }
}
