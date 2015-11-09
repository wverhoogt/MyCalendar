<?php namespace KurtJensen\MyCalendar\Components;

use Cms\Classes\ComponentBase;
use Cms\Classes\Page;
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
        return [
            'linkpage' => [
                'title' => 'Link to Page',
                'description' => 'Name of the event page file for the "More Details" links. This property is used by the event component partial.',
                'type' => 'dropdown',
                'default' => '',
                'group' => 'Links',
            ],
            'title_max' => [
                'title' => 'Maximum Popup Title Length',
                'description' => 'Maximum length of "title" property that shows the details of an event on hover.',
                'type' => 'text',
                'default' => 100,
            ],
        ];
    }

    public function getLinkpageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName')+['' => 'None - Use Modal Pop-up'];
    }

    public function onRun()
    {
        $this->page['MyEvents'] = $this->loadEvents();
    }

    public function loadEvents()
    {
        $MyEvents = [];
        $events = MyEvents::where('is_published', true)->
        where('month', '>=', date('m'))->
        where('year', '>=', date('Y'))->
        orderBy('year')->
        orderBy('month')->
        orderBy('day')->
        orderBy('time')->
        get();

        $maxLen = $this->property('title_max', 100);
        $linkPage = $this->property('linkpage', '');

        foreach ($events as $e) {
            $title = (strlen($e->text) > 50) ? substr(strip_tags($e->text), 0, $maxLen) . '...' : $e->text;

            $link = $e->link ? $e->link : ($linkPage ? Page::url($linkPage, ['slug' => $e->id]) :
                '#EventDetail"
            	data-request="onShowEvent"
            	data-request-data="evid:' . $e->id . '"
		        data-request-success="$(\'html, body\').animate({ scrollTop: 0 });"
            	data-request-update="\'Events::details\':\'#EventDetail\'" data-toggle="modal" data-target="#myModal');

            $MyEvents[$e->year][$e->month][$e->day][] = ['name' => $e->name . ' ' . $e->human_time, 'title' => $title, 'link' => $link];
        }
        return $MyEvents;

    }

    public function onShowEvent()
    {
        $slug = post('evid');
        if (!$e = MyEvents::where('is_published', true)->find($slug)) {
            return 'Event not found!';
        }

        $maxLen = $this->property('title_max', 100);

        $link = $e->link ? $e->link : '';
        $this->page['ev'] = ['name' => $e->name, 'date' => $e->date, 'time' => $e->human_time, 'link' => $link, 'text' => $e->text];
    }
}
