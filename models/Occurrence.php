<?php namespace KurtJensen\MyCalendar\Models;

use Model;

/**
 * Occurence Model
 */
class Occurrence extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'kurtjensen_mycalendar_occurrences';

    /**
     * @var array Guarded fields
     */
    protected $guarded = [];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [
        'event_id',
        'relation',
        'relation_id',
        'start_at',
        'end_at',
        //'is_modified',
        'is_allday',
        //'is_cancelled',
    ];

    /**
     * @var array Relations
     */
    public $belongsTo = [
        'events' => ['KurtJensen\MyCalendar\Models\Event',
            'table' => 'kurtjensen_mycal_events',
        ],
    ];

}
