<?php namespace KurtJensen\MyCalendar\Components;

use KurtJensen\MyCalendar\Components\Events;
use KurtJensen\MyCalendar\Components\Week;

class WeekEvents extends Week {
	public $EventsComp = null;

	public function componentDetails() {
		return [
			'name' => 'kurtjensen.mycalendar::lang.week_events.name',
			'description' => 'kurtjensen.mycalendar::lang.week_events.description',
		];
	}

	public function defineProperties() {
		$this->EventsComp = new Events('WeekEvents');
		$properties = $this->propertiesFor('week');
		return array_merge($properties, $this->EventsComp->defineProperties());
	}

	public function init() {
		$this->initFor('week');
		$this->EventsComp->importProperties($this);
	}

	public function onRun() {
		$this->mergeEvents($this->EventsComp->loadEvents());
	}

	public function onRender() {
		$this->renderFor('week');
	}

	public function onShowEvent() {
		return $this->page['ev'] = $this->EventsComp->onShowEvent();
	}

}
