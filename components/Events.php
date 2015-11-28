<?php namespace KurtJensen\MyCalendar\Components;

use Cms\Classes\ComponentBase;
use Cms\Classes\Page;
use KurtJensen\MyCalendar\Models\Category as Category;
use KurtJensen\MyCalendar\Models\CategorysEvents;
use KurtJensen\MyCalendar\Models\Event as MyEvents;
use KurtJensen\MyCalendar\Models\Settings;

class Events extends ComponentBase {
	use \KurtJensen\MyCalendar\Traits\LoadPermissions;

	public $usePermissions = 0;

	public function componentDetails() {
		return [
			'name' => 'Events Component',
			'description' => 'Get Events from DB and insert them into page',
		];
	}

	public function defineProperties() {
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
			'usePermissions' => [
				'title' => 'Use Permission',
				'description' => 'Use permissions to restrict what categories of events are shown based on roles.',
				'type' => 'dropdown',
				'default' => 0,
				'options' => [0 => 'No', 1 => 'Yes'],
			],
		];
	}

	public function getLinkpageOptions() {
		return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName') + ['' => 'None - Use Modal Pop-up'];
	}

	public function init() {
		$this->usePermissions = $this->property('usePermissions', 0);
	}

	public function onRun() {
		$this->page['MyEvents'] = $this->loadEvents();
	}

	public function loadEvents() {
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
				);
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

			$MyEvents[$e->year][$e->month][$e->day][] = ['name' => $e->name . ' ' . $e->human_time, 'title' => $title, 'link' => $link];
		}
		return $MyEvents;

	}

	public function onShowEvent() {
		$slug = post('evid');
		$e = MyEvents::with('categorys')->where('is_published', true)->find($slug);
		if (!$e) {
			return $this->page['ev'] = ['name' => 'Event not found!', 'cats' => $e->categorys->lists('name')];
		}

		if ($this->usePermissions) {
			$this->loadPermissions();
			$eventPerms = $e->categorys->lists('id');

			$Allow = Category::whereIn('permission_id', $this->permarray)
				->lists('id');

			$Deny = Category::where('permission_id', Settings::get('deny_perm'))
				->lists('id');

			if (!count(array_intersect($eventPerms, $Allow))) {
				return $this->page['ev'] = ['name' => 'Event not allowed!', 'cats' => $e->categorys->lists('name')];
			}

			if (count(array_intersect($eventPerms, $Deny))) {
				return $this->page['ev'] = ['name' => 'Event Prohibited!', 'cats' => $e->categorys->lists('name')];
			}

		}

		$maxLen = $this->property('title_max', 100);

		$link = $e->link ? $e->link : '';
		$this->page['ev'] = ['name' => $e->name, 'date' => $e->date, 'time' => $e->human_time, 'link' => $link, 'text' => $e->text, 'cats' => $e->categorys->lists('name')];
	}
}
