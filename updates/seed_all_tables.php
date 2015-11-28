<?php namespace KurtJensen\MyCalendar\Updates;

use October\Rain\Database\Updates\Seeder;
use ShahiemSeymor\Roles\Models\UserPermission;

class SeedAllTables extends Seeder
{

    public function run()
    {
        if (!UserPermission::where('name', '=', 'calendar_public')->first()) {
            UserPermission::create([
                'name' => 'calendar_public',
            ]);
        }

        if (!UserPermission::where('name', '=', 'calendar_deny_all')->first()) {
            UserPermission::create([
                'name' => 'calendar_deny_all',
            ]);
        }

    }
}
