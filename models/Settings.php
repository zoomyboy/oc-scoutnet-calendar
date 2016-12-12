<?php

namespace Zoomyboy\Scoutnetcalendar\Models;

use Model;

class Settings extends Model {
	public $implement = ['System.Behaviors.SettingsModel'];

	public $settingsCode = 'zoomyboy_scoutnetcalendar_settings';

	public $settingsFields = 'fields.yaml';
}


