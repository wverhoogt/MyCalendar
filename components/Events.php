<?php namespace KurtJensen\MyCalendar\Components;

use Cms\Classes\ComponentBase;
use Cms\Classes\Page;
use KurtJensen\MyCalendar\Models\Category as Category;
use KurtJensen\MyCalendar\Models\CategorysEvents;
use KurtJensen\MyCalendar\Models\Event as MyEvents;
use KurtJensen\MyCalendar\Models\Settings;
use Lang;

class Events extends ComponentBase
{
    use \KurtJensen\MyCalendar\Traits\LoadPermissions;

    public $usePermissions = 0;

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
                    0 => 'kurtjensen.mycalendar::lang.events_comp.permissions_opt_no',
                    1 => 'kurtjensen.mycalendar::lang.events_comp.permissions_opt_yes',
                ],
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
    }

    public function onRun()
    {
        $this->page['MyEvents'] = $this->loadEvents();
    }

    public function loadEvents()
    {
        $MyEvents = [];
        if ($this->usePermissions) {
            $this->loadPermissions();

            $query =
            MyEvents::whereIn('id',
                CategorysEvents::whereIn('category_id',
                    Category::whereIn('permission_id', $this->permarray)
                        ->lists('id')
                )
                    ->lists('event_id')
            )
                ->whereNotIn('id',
                    CategorysEvents::whereIn('category_id',
                        Category::where('permission_id', Settings::get('deny_perm'))
                            ->lists('id')
                    )
                        ->lists('event_id')
                )
                ->where('is_published', true);
        } else {
            $query =
            MyEvents::where('is_published', true);

        }
        $events = $query->where('month', '>=', date('m'))
                        ->where('year', '>=', date('Y'))
                        ->orderBy('time')
                        ->get();

//                    ->whereNotIn('permission_id', Settings::get('deny_perm'))

        $maxLen = $this->property('title_max', 100);
        $linkPage = $this->property('linkpage', '');

        foreach ($events as $e) {
            $title = (strlen($e->text) > 50) ? substr(strip_tags($e->text), 0, $maxLen) . '...' : $e->text;

            $link = $e->link ? $e->link : ($linkPage ? Page::url($linkPage, ['slug' => $e->id]) :
                '#EventDetail"
            	data-request="onShowEvent"
            	data-request-data="evid:' . $e->id . '"
            	data-request-update="\'Events::details\':\'#EventDetail\'" data-toggle="modal" data-target="#myModal');

            $MyEvents[$e->year][$e->month][$e->day][] = ['name' => $e->name . ' ' . $e->human_time,
                'title' => $title,
                'link' => $link,
                'id' => $e->id,
                'owner' => $e->user_id];
        }
        return $MyEvents;

    }

    public function onShowEvent()
    {
        $slug = post('evid');
        $e = MyEvents::with('categorys')->where('is_published', true)->find($slug);
        if (!$e) {
            return $this->page['ev'] = ['name' => 'kurtjensen.mycalendar::lang.event.error_not_found', 'cats' => $e->categorys->lists('name')];
        }

        if ($this->usePermissions) {
            $this->loadPermissions();
            $eventPerms = $e->categorys->lists('id');

            $Allow = Category::whereIn('permission_id', $this->permarray)
                ->lists('id');

            $Deny = Category::where('permission_id', Settings::get('deny_perm'))
                ->lists('id');

            if (!count(array_intersect($eventPerms, $Allow))) {
                return $this->page['ev'] = ['name' => 'kurtjensen.mycalendar::lang.event.error_allow_no', 'cats' => $e->categorys->lists('name')];
            }

            if (count(array_intersect($eventPerms, $Deny))) {
                return $this->page['ev'] = ['name' => 'kurtjensen.mycalendar::lang.event.error_prohibit', 'cats' => $e->categorys->lists('name')];
            }

        }

        $link = $e->link ? $e->link : '';
        $this->page['ev'] = ['name' => $e->name, 'date' => $e->date, 'time' => $e->human_time, 'link' => $link, 'text' => $e->text, 'cats' => $e->categorys->lists('name')];
    }
}
