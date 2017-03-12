<?php namespace KurtJensen\MyCalendar\Components;

use KurtJensen\MyCalendar\Components\Events;
use KurtJensen\MyCalendar\Components\Month;

class MonthEvents extends Month {
	public $EventsComp = null;

	public function componentDetails() {
		return [
			'name' => 'kurtjensen.mycalendar::lang.month_events.name',
			'description' => 'kurtjensen.mycalendar::lang.month_events.description',
		];
	}

	public function defineProperties() {
		$this->EventsComp = new Events('MonthEvents');
		$properties = $this->propertiesFor('month');
		return array_merge($properties, $this->EventsComp->defineProperties());
	}

	public function init() {
		$this->initFor('month');
		$this->EventsComp->importProperties($this);
	}

	public function onRun() {
		$this->mergeEvents($this->EventsComp->loadEvents());
	}

	public function onRender() {
		$this->renderFor('month');
	}

	public function onShowEvent() {
		return $this->page['ev'] = $this->EventsComp->onShowEvent();
	}

}
