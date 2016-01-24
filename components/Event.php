<?php namespace KurtJensen\MyCalendar\Components;

use Auth;
use Cms\Classes\ComponentBase;
use Cms\Classes\Page;
use KurtJensen\MyCalendar\Models\Event as MyEvents;
use KurtJensen\MyCalendar\Models\Settings;
use Lang;

class Event extends ComponentBase
{
    public $usePermissions = 0;
    public $user = null;
    public $calEvent = null;

    public function componentDetails()
    {
        return [
            'name' => 'kurtjensen.mycalendar::lang.event.name',
            'description' => 'kurtjensen.mycalendar::lang.event.description',
        ];
    }

    public function defineProperties()
    {
        return [
            'slug' => [
                'title' => 'kurtjensen.mycalendar::lang.event.slug_title',
                'description' => 'kurtjensen.mycalendar::lang.event.slug_description',
                'default' => '{{ :slug }}',
                'type' => 'string',
            ],
            'linkpage' => [
                'title' => 'kurtjensen.mycalendar::lang.event.link_title',
                'description' => 'kurtjensen.mycalendar::lang.event.link_desc',
                'type' => 'dropdown',
                'group' => 'kurtjensen.mycalendar::lang.event.link_group',
            ],
            'usePermissions' => [
                'title' => 'kurtjensen.mycalendar::lang.events_comp.permissions_title',
                'description' => 'kurtjensen.mycalendar::lang.events_comp.permissions_description',
                'type' => 'dropdown',
                'default' => 0,
                'options' => [
                    0 => 'kurtjensen.mycalendar::lang.events_comp.opt_no',
                    1 => 'kurtjensen.mycalendar::lang.events_comp.opt_yes',
                ],
            ],
        ];
    }

    public function init()
    {
        $this->usePermissions = $this->property('usePermissions', 0);
    }

    public function getLinkpageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    public function onRun()
    {
        $this->loadEvent();
        $this->page['backLink'] = $this->property('linkpage', '');
        $this->page->title = $this->calEvent->name;
        $date = isset($this->calEvent->date) ? $this->calEvent->date->format(Settings::get('date_format', 'F jS, Y')) : '';
        $time = isset($this->calEvent->time) ? $this->calEvent->carbon_time->format(Settings::get('time_format', 'g:i a')) : '';
        $this->page->description = $date . ' ' . $time;
    }

    public function loadEvent()
    {
        $slug = $this->property('slug');
        if ($this->usePermissions) {
            if (!$this->user) {
                $this->user = Auth::getUser();
            }

            $query = MyEvents::withOwner()
                ->permisions(
                    $this->user->id,
                    [Settings::get('public_perm')],
                    Settings::get('deny_perm')
                );
        } else {
            $query = MyEvents::withOwner();
        }

        $this->calEvent = $query->with('categorys')
             ->where('is_published', true)
             ->find($slug);

        if (!$this->calEvent) {
            return $this->page['ev'] = ['name' => Lang::get('kurtjensen.mycalendar::lang.event.error_not_found'), 'cats' => []];
        }

        return $this->page['ev'] = [
            'name' => $this->calEvent->name,
            'date' => $this->calEvent->date,
            'time' => $this->calEvent->human_time,
            'link' => $this->calEvent->link ? $this->calEvent->link : '',
            'text' => $this->calEvent->text,
            'cats' => $this->calEvent->categorys->lists('name'),
            'owner_name' => $this->calEvent->owner_name,
        ];
    }
}
