<?php namespace Tiipiik\Import\Controllers;

use BackendMenu;
use Backend\Classes\Controller;

/**
 * Imports Back-end Controller
 */
class Imports extends Controller
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
        BackendMenu::setContext('October.System', 'system', 'settings');
    }
    
}