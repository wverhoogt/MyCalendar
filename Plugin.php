<?php namespace KurtJensen\MyCalendar;

use Backend;
use RainLab\User\Models\User as UserModel;
use System\Classes\PluginBase;

/**
 * MyCalendar Plugin Information File
 */
class Plugin extends PluginBase
{

    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name' => 'MyCalendar',
            'description' => 'Super simple calendar for displaying events.',
            'author' => 'KurtJensen',
            'icon' => 'icon-birthday-cake',
        ];
    }

    public function boot()
    {

        UserModel::extend(function ($model) {
            $model->hasMany['mycalevents'] = [
                'KurtJensen\MyCalendar\Models\Events',
                'table' => 'kurtjensen_mycal_events',
                'key' => 'user_id',
                'otherKey' => 'id'];
        });
    }

    public function registerComponents()
    {
        return [
            'KurtJensen\MyCalendar\Components\Month' => 'Month',
            //'KurtJensen\MyCalendar\Components\Week' => 'Week',
            'KurtJensen\MyCalendar\Components\EvList' => 'EvList',
            'KurtJensen\MyCalendar\Components\Events' => 'Events',
            'KurtJensen\MyCalendar\Components\EventForm' => 'EventForm',
        ];
    }

    public function registerNavigation()
    {
        return [
            'mycalendar' => [
                'label' => 'MyCalendar',
                'icon' => 'icon-birthday-cake',
                'url' => Backend::url('kurtjensen/mycalendar/events'),
                'permissions' => ['kurtjensen.mycalendar.*'],
                'order' => 500,

                'sideMenu' => [
                    'events' => [
                        'label' => 'Events',
                        'url' => Backend::url('kurtjensen/mycalendar/events'),
                        'icon' => 'icon-birthday-cake',
                        'permissions' => ['kurtjensen.mycalendar.events'],
                    ],
                    'categories' => [
                        'label' => 'Categories',
                        'url' => Backend::url('kurtjensen/mycalendar/categories'),
                        'icon' => 'icon-folder',
                        'permissions' => ['kurtjensen.mycalendar.categories'],
                    ],
                ],
            ],
        ];
    }

    public function registerPermissions()
    {
        return [
            'kurtjensen.mycalendar.events' => ['label' => 'events', 'tab' => 'MyCalendar'],
            'kurtjensen.mycalendar.categories' => ['label' => 'categories', 'tab' => 'MyCalendar'],
        ];
    }

}
