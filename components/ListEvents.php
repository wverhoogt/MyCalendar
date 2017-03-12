<?php namespace KurtJensen\MyCalendar\Components;

use KurtJensen\MyCalendar\Components\Events;
use KurtJensen\MyCalendar\Components\EvList;

class ListEvents extends EvList {
	public $EventsComp = null;

	public function componentDetails() {
		return [
			'name' => 'kurtjensen.mycalendar::lang.list_events.name',
			'description' => 'kurtjensen.mycalendar::lang.list_events.description',
		];
	}

	public function defineProperties() {
		$this->EventsComp = new Events('ListEvents');
		return array_merge($this->propertiesFor('list'), $this->propertiesFor('events'));
	}

	public function init() {
		$this->initFor('list');
		$this->EventsComp->importProperties($this);
	}

	public function onRun() {
		$this->mergeEvents($this->EventsComp->loadEvents());
	}

	public function onRender() {
		$this->renderFor('list');
	}

	public function onShowEvent() {
		$this->EventsComp->compLink = 'ListEvents';
		return $this->page['ev'] = $this->EventsComp->onShowEvent();
	}
}
