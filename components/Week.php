<?php namespace KurtJensen\MyCalendar\Components;

use Cms\Classes\ComponentBase;

class Week extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name' => 'Week Component',
            'description' => 'No description provided yet...',
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        $this->page['MyEvents'] = [
            2 => [
                20 => [
                    ['txt' => 'test-2-20'],
                    ['link' => 'data-toggle="modal" href="#content-confirmation"', 'txt' => 'test-again', 'class' => 'text-success'],
                ],
                22 => [
                    ['txt' => 'test-2-22 test-2-22 test-2-22 test-2-22 test-2-22 test-2-22 test-2-22 test-2-22 test-2-22 test-2-22 v'],
                ],
            ],
        ];

        $this->page['MyDayProps'] = [
            2 => [
                20 => ['link' => 'data-toggle="modal" href="#content-confirmation"'],
                22 => ['class' => 'calendar-day-dis'],
            ],
        ];
    }

}
