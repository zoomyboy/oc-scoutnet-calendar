<?php

namespace Zoomyboy\Scoutnet\Controllers;

use Lang;

trait HasNestedList {
    public function getEnv() {
        $model = $this->formGetModel();

        return [
            'icon' => $model::$icon,
            'title' => $model->title ?: Lang::get($model::$tabTitle)
        ];
    }
}
