<?php

namespace Zoomyboy\Scoutnet\Models;

use Model;

class Settings extends Model {
    public $implement = ['System.Behaviors.SettingsModel'];

    public $settingsCode = 'zoomyboy_scoutnet';

    public $settingsFields = 'fields.yaml';
}


