<?php namespace KurtJensen\MyCalendar\Components;

use Carbon\Carbon;
use Cms\Classes\ComponentBase;
use Lang;

class Month extends ComponentBase {
	use \KurtJensen\MyCalendar\Traits\ComonProperties;

	public $monthTitle;
	public $linkNextMonth;
	public $linkPrevMonth;

	public function componentDetails() {
		return [
			'name' => 'kurtjensen.mycalendar::lang.month.name',
			'description' => 'kurtjensen.mycalendar::lang.month.description',
		];
	}

	public function defineProperties() {
		return $this->propertiesFor('month');
	}

	public function init() {
		if ($this->property('loadstyle')) {
			$this->addCss('/plugins/kurtjensen/mycalendar/assets/css/calendar.css');
		}
		$this->color = $this->property('color');
		$this->weekstart = $this->property('weekstart', 0);
	}

	public function onRender() {
		// Must use onRender() for properties that can be modified in page
		$y_start = date('Y') - 2;
		$y_end = $y_start + 15;

		$this->month = in_array($this->property('month'), range(1, 12)) ? $this->property('month') : date('m');
		$this->year = in_array($this->property('year'), range($y_start, $y_end)) ? $this->property('year') : date('Y');

		$this->calcElements();

		$this->dayprops = $this->property('dayprops');
		$this->events = $this->property('events');
	}

	public function calcElements() {
		$this->calHeadings = $this->getWeekstartOptions();
		$time = new Carbon($this->month . '/1/' . $this->year); // 11/01/2016
		$this->monthTitle = Lang::get('kurtjensen.mycalendar::lang.rrule.month.' . $time->month); // Nov
		$this->monthNum = $time->month; // 11
		$this->running_day = $time->dayOfWeek; // 2 ( Tuesday )
		$this->days_in_month = $time->daysInMonth; // 30

		//$this->weekstart = 1; // 1 ( Monday )

		$this->dayPointer = $this->weekstart - $this->running_day; // 1 - 2 = -1

		// go back another week if the daypointer into current month ( positive )
		if ($this->dayPointer > 0) {
			$this->dayPointer = $this->dayPointer - 7;
		}

		$prevMonthLastDay = $time->copy()->subMonth()->daysInMonth; // 31

		$this->prevMonthStartDay = $this->dayPointer + $prevMonthLastDay + 1; // -1 + 31 + 1 = 31

		$this->linkNextMonth = $time->copy()->addDays(32);

		$this->linkPrevMonth = $time->copy()->subDays(2);

	}

}
