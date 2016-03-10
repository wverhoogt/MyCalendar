<?php namespace KurtJensen\MyCalendar\Components;

use Carbon\Carbon;
use Cms\Classes\ComponentBase;
//use Simshaun\Recurr;
use \Recurr\Rule;
use \Recurr\Transformer\ArrayTransformer;

class Test extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name' => 'Test Component',
            'description' => 'No description provided yet...',
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {

        // $timezone = 'America/New_York';
        //$startDate = new \DateTime('2013-06-12 20:00:00', new \DateTimeZone($timezone));
        // $endDate = new \DateTime('2013-06-14 20:00:00', new \DateTimeZone($timezone)); // Optional
        //  $rule = new \Recurr\Rule('FREQ=MONTHLY;COUNT=5', $startDate, $endDate, $timezone);
        //  $transformer = new \Recurr\Transformer\ArrayTransformer();

        $start = new Carbon("2015-01-01");
        $time = new Carbon("8:00");
        $start->addMinutes($time->minute);
        $start->addHours($time->hour);
        $end = $start->copy();
        $end->addMinutes(3);
        $end->addHours(12);

        $rules = new \Recurr\Rule("FREQ=WEEKLY;COUNT=30;WKST=MO", $start, $end);
        $transformer = new \Recurr\Transformer\ArrayTransformer;
        $this->page['dates'] = $transformer->transform($rules);
    }

}
