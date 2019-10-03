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

    public function update_onEdit($recordId = null, $context = null)
    {
        $this->vars['mode'] = 'editing';
        parent::update($recordId, $context);

        return [
            'env' => $this->getEnv(),
            'content' => $this->makePartial('update', [
                'form' => $this->widget->form,
            ])
        ];
    }

    public function onCreate()
    {
        $this->vars['mode'] = 'adding';
        parent::create();

        return [
            'env' => $this->getEnv(),
            'content' => $this->makePartial('create', [
                'form' => $this->widget->form,
            ])
        ];
    }

    public function create_onSave() {
        parent::create_onSave();

        return ['model' => $this->formGetModel(), 'env' => $this->getEnv() ];
    }

    public function update_onSave($recordId = null, $context = null) {
        parent::update_onSave($recordId, $context);

        return ['model' => $this->formGetModel(), 'env' => $this->getEnv() ];
    }
}
