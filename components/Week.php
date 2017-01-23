<?php namespace KurtJensen\MyCalendar\Components;

use Carbon\Carbon;
use Cms\Classes\ComponentBase;

class Week extends ComponentBase {
	use \KurtJensen\MyCalendar\Traits\ComonProperties;

	public function componentDetails() {
		return [
			'name' => 'kurtjensen.mycalendar::lang.week.name',
			'description' => 'kurtjensen.mycalendar::lang.week.description',
		];
	}

	public function defineProperties() {
		return $this->propertiesFor('week');
	}

	public function init() {
		if ($this->property('loadstyle')) {
			$this->addCss('/plugins/kurtjensen/mycalendar/assets/css/calendar.css');
		}

		$this->day = $this->property('day') ?: date('d');
		$this->month = $this->property('month') ?: date('m');
		$this->year = $this->property('year') ?: date('Y');
		$this->weekstart = $this->property('weekstart', 0);
		$this->calcElements();
		$this->color = $this->property('color');
	}

	public function onRender() {
		// Must use onRender() for properties that can be modified in page
		$this->dayprops = $this->property('dayprops');
		$this->events = $this->property('events');
	}

	public function calcElements() {

		$this->calHeadings = $this->getWeekstartOptions();

		$time = new Carbon($this->year . '-' . $this->month . '-' . $this->day);

		$this->running_day = $time->dayOfWeek;

		$this->monthNum = $time->month;

		$this->days_in_month = $time->daysInMonth;

		$this->dayPointer = $this->day - $this->running_day - $this->weekstart - 1;

		$prevMonthLastDay = $time->copy()->subMonth()->daysInMonth;

		$this->prevMonthStartDay = $this->dayPointer + $prevMonthLastDay + 1;

	}

}
