<?php namespace KurtJensen\MyCalendar\Components;

use Cms\Classes\ComponentBase;
use Cms\Classes\Page;
use KurtJensen\MyCalendar\Models\Event as MyEvents;

class Event extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name' => 'kurtjensen.mycalendar::lang.events_comp.name',
            'description' => 'kurtjensen.mycalendar::lang.events_comp.description',
        ];
    }

    public function defineProperties()
    {
        return [
            'slug' => [
                'title' => 'kurtjensen.mycalendar::lang.event.slug_title',
                'description' => 'kurtjensen.mycalendar::lang.events_comp.slug_description',
                'default' => '{{ :slug }}',
                'type' => 'string',
            ],
            'linkpage' => [
                'title' => 'kurtjensen.mycalendar::lang.event.linkpage_title',
                'description' => 'kurtjensen.mycalendar::lang.events_comp.linkpage_desc',
                'type' => 'dropdown',
                'default' => '',
                'group' => 'kurtjensen.mycalendar::lang.event.linkpage_group',
            ],
        ];
    }

    public function getLinkpageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName')+['' => 'kurtjensen.mycalendar::lang.events_comp.linkpage_opt_none'];
    }

    public function onRun()
    {
        $this->page['ev'] = $this->loadEvents();
        $this->page['backLink'] = $this->property('linkpage', '');
    }

    public function loadEvents()
    {
        $slug = $this->property('slug');
        if (!$e = MyEvents::where('is_published', true)->find($slug)) {
            return 'Event not found!';
        }

        $maxLen = $this->property('title_max', 100);

        $link = $e->link ? $e->link : '';
        return ['name' => $e->name, 'date' => $e->date, 'time' => $e->human_time, 'link' => $link, 'text' => $e->text];
    }
}
