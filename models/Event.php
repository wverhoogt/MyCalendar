<?php namespace KurtJensen\MyCalendar\Models;

use Model;
use RainLab\User\Models\User;

/**
 * event Model
 */
class Event extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'kurtjensen_mycal_events';

    /**
     * @var array Guarded fields
     */
    protected $guarded = [];

    /**
     * @var array Fillable fields
     */
    protected $fillable = ['*'];

    /**
     * @var array Relations
     */

    public $belongsTo = [
        'user' => ['RainLab\User\Models\User',
            'key' => 'user_id',
            'otherKey' => 'id'],
    ];

    public $belongsToMany = [
        'categories' => ['KurtJensen\MyCalendar\Models\Category', 'table' => 'kurtjensen_mycal_events_categories', 'key' => 'events_id', 'otherKey' => 'category_id'],
    ];
/*
id
user_id
name
day
month
year
text
is_published
 */
    public $attributes = [
        'date' => '',
        'human_time' => '',
    ];

    public function getDateAttribute()
    {
        if (!$this->year) {
            return date('Y-m-d');
        }

        return $this->year . '-' . $this->month . '-' . $this->day;
    }

    public function getHumanTimeAttribute()
    {
        if (!$this->time) {
            return '';
        }
        list($h, $m) = explode(':', $this->time);
        $time = ($h > 12 ? ($h - 12) : $h) . ':' . $m . ($h > 11 ? 'pm' : 'am');
        return $time;
    }

    public function beforeSave()
    {
        list($this->year, $this->month, $this->day) = explode('-', $this->attributes['date']);

        unset($this->attributes['date'], $this->attributes['human_time']);
    }

    public function getDayOptions($month)
    {
        if ($this->month && $this->year) {
            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $this->month, $this->year);
            $days = range(1, $daysInMonth);
            return array_combine($days, $days);
        }
        return [0 => 'Pick a Month AND Year'];
    }

    public function getMonthOptions()
    {
        $months = ['0', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        unset($months[0]);
        return $months;
    }

    public function getYearOptions()
    {
        $year = date('Y');
        $years = range($year, $year + 5);
        return array_combine($years, $years);
    }

    public function getUserIdOptions($keyValue = null)
    {
        foreach (User::orderBy('name')->get() as $user) {
            $Users[$user->id] = $user->surname . ', ' . $user->name;
        }

        return $Users;
    }

}
