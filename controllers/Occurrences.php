<?php namespace KurtJensen\MyCalendar\Controllers;

use BackendMenu;
use Backend\Classes\Controller;

/**
 * Occurrences Back-end Controller
 */
class Occurrences extends Controller
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

        BackendMenu::setContext('KurtJensen.MyCalendar', 'mycalendar', 'occurrences');
    }
}