<?php namespace KurtJensen\MyCalendar\Components;

use Cms\Classes\ComponentBase;

class EvList extends ComponentBase {
	use \KurtJensen\MyCalendar\Traits\ComonProperties;

	public function componentDetails() {
		return [
			'name' => 'kurtjensen.mycalendar::lang.evlist.name',
			'description' => 'kurtjensen.mycalendar::lang.evlist.description',
		];
	}

	public function defineProperties() {
		return $this->propertiesFor('list');
	}

	public function init() {
		if ($this->property('loadstyle')) {
			$this->addCss('/plugins/kurtjensen/mycalendar/assets/css/calendar.css');
		}
		$this->color = $this->property('color');
	}

	public function onRender() {
		// Must use onRender() for properties that can be modified in page
		$this->events = $this->property('events');
	}
}
