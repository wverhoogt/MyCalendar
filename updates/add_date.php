<?php namespace KurtJensen\MyCalendar\Updates;
use DB;
use October\Rain\Database\Updates\Migration;
use Schema;

class AddDate extends Migration {

	public function up() {
		Schema::table('kurtjensen_mycal_events', function ($table) {
			$table->date('date')->nullable()->after('day');
		});

		DB::table('kurtjensen_mycal_events as enew')
			->join('kurtjensen_mycal_events as eold', 'eold.id', '=', 'enew.id')
			->update(['enew.date' => DB::raw('CONCAT(eold.year, "-", eold.month, "-",eold.day)')]);

	}

	public function down() {
		Schema::table('kurtjensen_mycal_events', function ($table) {
			$table->dropColumn('date');
		});
	}

}
