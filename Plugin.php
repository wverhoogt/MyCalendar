<?php namespace KurtJensen\MyCalendar;

use Backend;
use KurtJensen\MyCalendar\Controllers\Events as EventController;
use KurtJensen\MyCalendar\Models\Event as EventModel;
//use October\Rain\Auth\Models\User as BackUser;
use RainLab\User\Models\User;
use System\Classes\PluginBase;
use System\Classes\PluginManager;

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
        $manager = PluginManager::instance();
        if ($manager->exists('rainlab.user')) {
            User::extend(function ($model) {
                $model->hasMany['mycalevents'] = [
                    'KurtJensen\MyCalendar\Models\Events',
                    'table' => 'kurtjensen_mycal_events'];
            });

            EventModel::extend(function ($model) {
                $model->belongsTo['user'] = [
                    'RainLab\User\Models\User',
                    'table' => 'user',
                    'key' => 'user_id',
                    'otherKey' => 'id'];
            });

            EventController::extendFormFields(function ($form, $model, $context) {

                if (!$model instanceof EventModel) {
                    return;
                }

                $form->addFields([
                    'user_id' => [
                        'label' => 'Creator',
                        'type' => 'dropdown',
                        'span' => 'right',
                    ],
                ]);
            });

            EventController::extendListColumns(function ($lists, $model) {
                $lists->addColumns([
                    'fname' => [
                        'label' => 'Creator First',
                        'relation' => 'user',
                        'select' => 'name',
                        'searchable' => 'true',
                        'sortable' => 'true',
                    ],
                    'lname' => [
                        'label' => 'Creator Last',
                        'relation' => 'user',
                        'select' => 'surname',
                        'searchable' => 'true',
                        'sortable' => 'true',
                    ],
                ]);
            });
        }

    }

    public function registerComponents()
    {
        return [
            'KurtJensen\MyCalendar\Components\Month' => 'Month',
            //'KurtJensen\MyCalendar\Components\Week' => 'Week',
            'KurtJensen\MyCalendar\Components\EvList' => 'EvList',
            'KurtJensen\MyCalendar\Components\Events' => 'Events',
            'KurtJensen\MyCalendar\Components\Event' => 'Event',
            'KurtJensen\MyCalendar\Components\EventForm' => 'EventForm',
        ];
    }

    public function registerNavigation()
    {
        $navMenu = [
            'mycalendar' => [
                'label' => 'MyCalendar',
                'icon' => 'icon-birthday-cake',
                'url' => Backend::url('kurtjensen/mycalendar/events'),
                'permissions' => ['kurtjensen.mycalendar.*'],
                'order' => 500,

                'sideMenu' => [
                    'categories' => [
                        'label' => 'Categories',
                        'url' => Backend::url('kurtjensen/mycalendar/categories'),
                        'icon' => 'icon-folder',
                        'permissions' => ['kurtjensen.mycalendar.categories'],
                    ],
                ],
            ],
        ];

        if (class_exists('UserModel')) {
            $navMenu['mycalendar']['sideMenu']['events'] = [
                'label' => 'Events',
                'url' => Backend::url('kurtjensen/mycalendar/events'),
                'icon' => 'icon-birthday-cake',
                'permissions' => ['kurtjensen.mycalendar.events'],
            ];
        }

        return $navMenu;
    }

    public function registerPermissions()
    {
        return [
            'kurtjensen.mycalendar.events' => ['label' => 'events', 'tab' => 'MyCalendar'],
            'kurtjensen.mycalendar.categories' => ['label' => 'categories', 'tab' => 'MyCalendar'],
        ];
    }

    public function registerSettings()
    {
        return [
            'settings' => [
                'label' => 'Calendar',
                'icon' => 'icon-birthday-cake',
                'description' => 'Configure calendar category protection.',
                'class' => 'KurtJensen\MyCalendar\Models\Settings',
                'order' => 199,
            ],
        ];

    }

}
