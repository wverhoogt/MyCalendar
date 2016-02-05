<?php namespace KurtJensen\MyCalendar\Components;

use Auth;
use Cms\Classes\ComponentBase;
use Cms\Classes\Page;
use KurtJensen\MyCalendar\Models\Event as MyEvents;
use KurtJensen\MyCalendar\Models\Settings;
use Lang;

class Events extends ComponentBase
{
    //use \KurtJensen\MyCalendar\Traits\LoadPermissions;

    public $usePermissions = 0;
    public $dayspast = 0;
    public $daysfuture = 0;
    public $compLink = 'Events';
    public $user_id = null;

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
            'linkpage' => [
                'title' => 'kurtjensen.mycalendar::lang.events_comp.linkpage_title',
                'description' => 'kurtjensen.mycalendar::lang.events_comp.linkpage_desc',
                'type' => 'dropdown',
                'default' => '',
                'group' => 'kurtjensen.mycalendar::lang.events_comp.linkpage_group',
            ],
            'title_max' => [
                'title' => 'kurtjensen.mycalendar::lang.events_comp.title_max_title',
                'description' => 'kurtjensen.mycalendar::lang.events_comp.title_max_description',
                'default' => 100,
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
            'dayspast' => [
                'title' => 'kurtjensen.mycalendar::lang.events_comp.past_title',
                'description' => 'kurtjensen.mycalendar::lang.events_comp.past_description',
                'default' => 0,
            ],
            'daysfuture' => [
                'title' => 'kurtjensen.mycalendar::lang.events_comp.future_title',
                'description' => 'kurtjensen.mycalendar::lang.events_comp.future_description',
                'default' => 60,
            ],
        ];
    }

    public function getLinkpageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName')+
        ['' => Lang::get('kurtjensen.mycalendar::lang.events_comp.linkpage_opt_none')];
    }

    public function init()
    {
        $this->usePermissions = $this->property('usePermissions', 0);
        $this->dayspast = $this->property('dayspast', 0);
        $this->daysfuture = $this->property('daysfuture', 60);
    }

    public function onRun()
    {
        $this->page['MyEvents'] = $this->loadEvents();
    }

    public function userId()
    {
        if (is_null($this->user_id)) {
            $user = Auth::getUser();
        }

        if ($user) {
            $this->user_id = $user->id;
        } else {
            $this->user_id = 0;
        }
        return $this->user_id;
    }

    public function loadEvents()
    {
        $MyEvents = [];

        $query = MyEvents::withOwner()
            ->published()
            ->past($this->dayspast)
            ->future($this->daysfuture)
            ->orderBy('date')
            ->orderBy('time');

        if ($this->usePermissions) {

            $query->permisions(
                $this->userId(),
                [Settings::get('public_perm')],
                Settings::get('deny_perm')
            );
        }

        $events = $query->get();

        $maxLen = $this->property('title_max', 100);
        $linkPage = $this->property('linkpage', '');

        foreach ($events as $e) {
            $title = (strlen($e->text) > 50) ? substr(strip_tags($e->text), 0, $maxLen) . '...' : $e->text;

            $link = $e->link ? $e->link : ($linkPage ? Page::url($linkPage, ['slug' => $e->id]) :
                '#EventDetail"
            	data-request="onShowEvent"
            	data-request-data="evid:' . $e->id . '"
            	data-request-update="\'' . $this->compLink . '::details\':\'#EventDetail\'" data-toggle="modal" data-target="#myModal');

            $MyEvents[$e->year][$e->month][$e->day][] = [
                'name' => $e->name . ' ' . $e->human_time,
                'title' => $title,
                'link' => $link,
                'id' => $e->id,
                'owner' => $e->user_id,
                'owner_name' => $e->owner_name,
                'data' => $e,
            ];
        }
        return $MyEvents;

    }

    public function onShowEvent()
    {
        $slug = post('evid');
        if ($this->usePermissions) {

            $query = MyEvents::withOwner()
                ->permisions(
                    $this->userId(),
                    [Settings::get('public_perm')],
                    Settings::get('deny_perm')
                );
        } else {
            $query = MyEvents::withOwner();
        }

        $e = $query->with('categorys')
                   ->where('is_published', true)
                   ->find($slug);

        if (!$e) {
            return $this->page['ev'] = ['name' => Lang::get('kurtjensen.mycalendar::lang.event.error_not_found'), 'cats' => []];
        }

        return $this->page['ev'] = [
            'name' => $e->name,
            'date' => $e->date->format(Settings::get('date_format', 'F jS, Y')),
            'time' => $e->human_time,
            'link' => $e->link ? $e->link : '',
            'text' => $e->text,
            'cats' => $e->categorys->lists('name'),
            'owner' => $e->user_id,
            'owner_name' => $e->owner_name,
            'data' => $e,
        ];
    }
}
