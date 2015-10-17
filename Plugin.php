<?php namespace KurtJensen\MyCalendar;

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
            'description' => 'No description provided yet...',
            'author' => 'KurtJensen',
            'icon' => 'icon-leaf',
        ];
    }

    public function registerComponents()
    {
        return [
            'KurtJensen\MyCalendar\Components\Month' => 'Month',
            'KurtJensen\MyCalendar\Components\Week' => 'Week',
            'KurtJensen\MyCalendar\Components\EvList' => 'EvList',
        ];
    }

}
