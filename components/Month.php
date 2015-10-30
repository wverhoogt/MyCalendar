<?php namespace KurtJensen\MyCalendar\Components;

use Carbon\Carbon;
use Cms\Classes\ComponentBase;

class Month extends ComponentBase
{
    public $month;
    public $year;
    public $dayprops;
    public $color;
    public $events;
    public $calHeadings;

    public $monthTitle;
    public $monthNum;
    public $running_day;
    public $days_in_month;
    public $dayPointer;
    public $prevMonthLastDay;
    public $prevMonthMonday;

    public function componentDetails()
    {
        return [
            'name' => 'Month Component',
            'description' => 'Shows a month calendar with events',
        ];
    }

    public function defineProperties()
    {
        return [
            'month' => [
                'title' => 'Month',
                'description' => 'The month you want to show.',
            ],
            'year' => [
                'title' => 'Year',
                'description' => 'The year you want to show.',
            ],
            'events' => [
                'title' => 'Events',
                'description' => 'Array of the events you want to show.',
            ],
            'color' => [
                'title' => 'Calendar Color',
                'description' => 'What color do you want calendar to be?',
                'type' => 'dropdown',
                'default' => 'red',
            ],
            'dayprops' => [
                'title' => 'Day Properties',
                'description' => 'Array of the properties you want to put on the day indicator.',
            ],
            'loadstyle' => [
                'title' => 'Load Style Sheet',
                'description' => 'Load the default CSS file.',
                'type' => 'dropdown',
                'default' => '1',
                'options' => [0 => 'No', 1 => 'Yes'],
            ],
        ];
    }

    public function getColorOptions()
    {
        return ['red' => 'red', 'green' => 'green', 'blue' => 'blue', 'yellow' => 'yellow'];
    }

    public function onRender()
    {
        if ($this->property('loadstyle')) {
            $this->addCss('/plugins/kurtjensen/mycalendar/assets/css/calendar.css');
        }

        $this->month = $this->property('month', date('m'));
        $this->year = $this->property('year', date('Y'));
        $this->calcElements();
        $this->dayprops = $this->property('dayprops');
        $this->color = $this->property('color');
        $this->events = $this->property('events');
    }

    public function calcElements()
    {

        $this->calHeadings = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        $time = new Carbon($this->month . '/1/' . $this->year);
        $this->monthTitle = $time->format('F');
        $this->monthNum = $time->month;
        $this->running_day = $time->dayOfWeek;
        $this->days_in_month = $time->daysInMonth;
        $this->dayPointer = 0 - $this->running_day;
        $prevMonthLastDay = $time->copy()->subMonth()->daysInMonth;
        $this->prevMonthMonday = $this->dayPointer + $prevMonthLastDay + 1;

    }

}
