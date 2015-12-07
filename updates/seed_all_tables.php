<?php namespace KurtJensen\MyCalendar\Updates;

use October\Rain\Database\Updates\Seeder;
use System\Classes\PluginManager;

class SeedAllTables extends Seeder {

	public function run() {
		$manager = PluginManager::instance();
		if ($manager->exists('shahiemseymor.roles')) {

			if (!ShahiemSeymor\Roles\Models\UserPermission::where('name', '=', 'calendar_public')->first()) {
				ShahiemSeymor\Roles\Models\UserPermission::create([
					'name' => 'calendar_public',
				]);
			}

			if (!ShahiemSeymor\Roles\Models\UserPermission::where('name', '=', 'calendar_deny_all')->first()) {
				ShahiemSeymor\Roles\Models\UserPermission::create([
					'name' => 'calendar_deny_all',
				]);
			}
		}

	}
}
