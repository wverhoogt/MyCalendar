<?php namespace KurtJensen\MyCalendar\Models;

use Model;

/**
 * Category Model
 */
class Category extends Model
{
    use \October\Rain\Database\Traits\Validation;

    public $table = 'kurtjensen_mycal_categories';

    /*
     * Validation


    name
    slug
    description
     */
    public $rules = [
        'name' => 'required',
        'slug' => 'required|between:3,64|unique:kurtjensen_mycal_categories',
    ];

    protected $guarded = [];

    public $belongsToMany = [
        'events' => ['KurtJensen\MyCalendar\Models\Event', 'table' => 'kurtjensen_mycal_events_categories', 'key' => 'category_id', 'otherKey' => 'events_id'],
    ];

    public function beforeValidate()
    {
        // Generate a URL slug for this model
        if (!$this->exists && !$this->slug) {
            $this->slug = Str::slug($this->name);
        }

    }

    public function afterDelete()
    {
        $this->events()->detach();
    }

    public function getEventCountAttribute()
    {
        return $this->events()->count();
    }
}
