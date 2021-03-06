<?php

namespace Zoomyboy\Scoutnet\Models;

use Model;

class Setting extends Model {
    public $implement = ['System.Behaviors.SettingsModel'];

    public $settingsCode = 'zoomyboy_scoutnet';

    public $settingsFields = 'fields.yaml';

}
