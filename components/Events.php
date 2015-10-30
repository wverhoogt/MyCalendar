<?php namespace KurtJensen\MyCalendar\Components;

use Cms\Classes\ComponentBase;
use KurtJensen\MyCalendar\Models\Event as MyEvents;

class Events extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name' => 'Events Component',
            'description' => 'Get Events from DB and insert them into page',
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        /*
        $this->page['MyEvents'] = [
        2015 => [
        2 => [
        20 => [
        ['txt' => 'test-2-20'],
        ['link' => 'data-toggle="modal" href="#content-confirmation"', 'txt' => 'test-again', 'class' => 'text-success'],
        ],
        22 => [
        ['txt' => 'test-2-22 test-2-22 test-2-22 test-2-22 test-2-22 test-2-22 test-2-22 test-2-22 test-2-22 test-2-22 v'],
        ],
        ],
        ]
        ];

        $this->page['MyDayProps'] = [
        2 => [
        20 => ['link' => 'data-toggle="modal" href="#content-confirmation"'],
        22 => ['class' => 'dis'],
        ],
        ];
         */
        $this->page['MyEvents'] = $this->loadEvents();
    }

    public function loadEvents()
    {
        $events = MyEvents::where('month', '>=', date('m'))->
        where('year', '>=', date('Y'))->get();

        foreach ($events as $e) {
            $MyEvents[$e->year][$e->month][$e->day][] = ['txt' => $e->name, 'title' => $e->name . ' - ' . $e->text];
        }
        return $MyEvents;

    }
}
