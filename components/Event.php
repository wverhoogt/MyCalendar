<?php namespace KurtJensen\MyCalendar\Components;

use Cms\Classes\ComponentBase;
use Cms\Classes\Page;
use KurtJensen\MyCalendar\Models\Event as MyEvents;

class Event extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name' => 'Event Component',
            'description' => 'Shows one event on page with details',
        ];
    }

    public function defineProperties()
    {
        return [
            'slug' => [
                'title' => 'Event Slug',
                'description' => 'URL slug to indicate Event ID to view on page',
                'default' => '{{ :slug }}',
                'type' => 'string',
            ],
            'link_page' => [
                'title' => 'Link to Page',
                'description' => 'Name of the event page file for list or calendar page. This property is used by the event component partial.',
                'type' => 'dropdown',
                'default' => 'cal/events',
                'group' => 'Links',
            ],
        ];
    }

    public function getLinkPageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    public function onRun()
    {
        $this->page['ev'] = $this->loadEvents();
        $this->page['backLink'] = $this->property('link_page', '');
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
