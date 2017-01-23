<?php namespace KurtJensen\MyCalendar\Traits;

/**
 * RRule
 * Renders RRule fields.
 *
 * @package kurtjensen\mycalendar
 * @author Kurt Jensen
 */
use Lang;

trait ComonProperties {
	// Week
	public $day;

	// Month - Week
	public $month;
	public $year;
	public $weekstart;
	public $dayprops;
	// ---  Calc Vars
	public $calHeadings;
	public $monthNum;
	public $running_day;
	public $days_in_month;
	public $dayPointer;
	public $prevMonthLastDay;
	public $prevMonthStartDay;

	// Month - Week - List
	public $color;
	public $events;

	public $langPath = 'kurtjensen.mycalendar::lang.';

	public function propertiesFor($type) {
		$properties = [];
		switch ($type) {
		case 'week':
			$properties = [
				// Week
				'day' => [
					'title' => $this->langPath . 'com_prop_trait.day_title',
					'description' => $this->langPath . 'com_prop_trait.day_description',
					'default' => '{{ :day }}',
				],
			];
		case 'month':
			$properties = array_merge($properties,
				[
					// Month - Week
					'month' => [
						'title' => $this->langPath . 'com_prop_trait.month_title',
						'description' => $this->langPath . 'com_prop_trait.month_description',
						'default' => '{{ :month }}',
					],
					'year' => [
						'title' => $this->langPath . 'com_prop_trait.year_title',
						'description' => $this->langPath . 'com_prop_trait.year_description',
						'default' => '{{ :year }}',
					],
					'weekstart' => [
						'title' => $this->langPath . 'com_prop_trait.weekstart_title',
						'description' => $this->langPath . 'com_prop_trait.weekstart_description',
						'type' => 'dropdown',
						'default' => '0',
					],
					'dayprops' => [
						'title' => $this->langPath . 'com_prop_trait.dayprops_title',
						'description' => $this->langPath . 'com_prop_trait.dayprops_description',
					],
				]);
		case 'list':
			$properties = array_merge($properties,
				[
					// Month - Week - List
					'events' => [
						'title' => $this->langPath . 'com_prop_trait.events_title',
						'description' => $this->langPath . 'com_prop_trait.events_description',
					],
					'color' => [
						'title' => $this->langPath . 'com_prop_trait.color_title',
						'description' => $this->langPath . 'com_prop_trait.color_description',
						'type' => 'dropdown',
						'default' => 'red',
					],
					'loadstyle' => [
						'title' => $this->langPath . 'com_prop_trait.loadstyle_title',
						'description' => $this->langPath . 'com_prop_trait.loadstyle_description',
						'type' => 'dropdown',
						'default' => '1',
						'options' => [
							0 => $this->langPath . 'com_prop_trait.opt_no',
							1 => $this->langPath . 'com_prop_trait.opt_yes',
						],
					],
				]);
		}
		return $properties;
	}

	public function trans($string) {
		return Lang::get($string);
	}

	public function getColorOptions() {
		$colors = [
			'red' => Lang::get($this->langPath . 'com_prop_trait.color_red'),
			'green' => Lang::get($this->langPath . 'com_prop_trait.color_green'),
			'blue' => Lang::get($this->langPath . 'com_prop_trait.color_blue'),
			'yellow' => Lang::get($this->langPath . 'com_prop_trait.color_yellow'),
		];
		return $colors;
	}

	public function getWeekstartOptions() {
		return [
			Lang::get($this->langPath . 'com_prop_trait.day_sun'),
			Lang::get($this->langPath . 'com_prop_trait.day_mon'),
			Lang::get($this->langPath . 'com_prop_trait.day_tue'),
			Lang::get($this->langPath . 'com_prop_trait.day_wed'),
			Lang::get($this->langPath . 'com_prop_trait.day_thu'),
			Lang::get($this->langPath . 'com_prop_trait.day_fri'),
			Lang::get($this->langPath . 'com_prop_trait.day_sat'),
		];
	}
}
