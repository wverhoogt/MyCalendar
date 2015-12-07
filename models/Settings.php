<?php namespace KurtJensen\MyCalendar\Models;

use Model;
use System\Classes\PluginManager;

/**
 * Settings Model
 */
class Settings extends Model {
	use \October\Rain\Database\Traits\Validation;

	public $implement = ['System.Behaviors.SettingsModel'];

	public $settingsCode = 'kurtjensen_calendar_settings';

	public $settingsFields = 'fields.yaml';

	/**
	 * Validation rules
	 */
	public $rules = [
		'public_perm' => 'required',
		'deny_perm' => 'required',
		'default_perm' => 'required',
	];

	/**
	 * @var array Relations
	 */
	public $belongsTo = [
		'permission' => ['ShahiemSeymor\Roles\Models\UserPermission',
			'otherKey' => 'id'],
	];

	public function __construct() {
		parent::__construct();
		$options = $this->getDropdownOptions();

		$this->public_perm = $this->public_perm ? $this->public_perm :
		array_search('calendar_public', $options);

		$this->deny_perm = $this->deny_perm ? $this->deny_perm :
		array_search('calendar_deny_all', $options);

		$this->default_perm = $this->default_perm ? $this->default_perm :
		array_search('calendar_deny_all', $options);
	}

	public function getDropdownOptions($fieldName = null, $keyValue = null) {
		$options = [];
		$manager = PluginManager::instance();
		if ($manager->exists('shahiemseymor.roles')) {
			$permissions = ShahiemSeymor\Roles\Models\UserPermission::get();
			foreach ($permissions as $permission) {
				$options[$permission->id] = $permission->name;
			}
		}
		return $options;
	}

}
