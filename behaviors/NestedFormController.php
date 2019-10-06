<?php

namespace Zoomyboy\Scoutnet\Behaviors;

use Lang;
use Request;
use \Backend\Behaviors\FormController;

class NestedFormController extends FormController {
    use HasNested;

    public function getEnv() {
        $model = $this->formGetModel();

        return [
            'icon' => $model::$icon,
            'title' => $model->title ?: Lang::get($model::$tabTitle)
        ];
    }

    /**
     * Associate a Child model with the parent.
     *
     * This happens when the request contains a parent key - this usally happens
     * when the create form is rendered (on onCreate).
     *
     * You should set the name of the parent relation on the Controller and define
     * a hidden field in the fields of the child model to hold the parent ID.
     */
    public function formExtendModel($model) {
        if (Request::filled('parent')) {
            $parentKey = $model->{$this->controller->parent}()->getForeignKey();
            $parentParts = explode('-', Request::input('parent'));
            $model->fill([$parentKey => $parentParts[1]]);
        }

        return $model;
    }

    public function update($recordId = null, $context = null)
    {
        $this->initNestedPage();

        $this->controller->makeLists();

        // On update we will render a list. But we should make sure that any AJAX request from 
        // form widgets will still reach the desired location - so we init a form even though
        // it is not displayed on the page.
        $model = $this->controller->formFindModelObject($recordId);
        $this->initForm($model);
        $this->controller->widget->calendarList->setActive($model);
    }

    public function update_onEdit($recordId = null, $context = null)
    {
        $this->controller->vars['mode'] = 'editing';
        parent::update($recordId, $context);

        return [
            'env' => $this->getEnv(),
            'content' => $this->controller->makePartial('update', [
                'form' => $this->controller->widget->form,
            ])
        ];
    }

    public function onCreate()
    {
        $this->controller->vars['mode'] = 'adding';
        parent::create();

        return [
            'env' => $this->getEnv(),
            'content' => $this->controller->makePartial('create', [
                'form' => $this->controller->widget->form,
            ])
        ];
    }

    public function create_onSave($context = null) {
        parent::create_onSave();

        return ['model' => $this->formGetModel(), 'env' => $this->getEnv() ];
    }

    public function update_onSave($recordId = null, $context = null) {
        parent::update_onSave($recordId, $context);

        return ['model' => $this->formGetModel(), 'env' => $this->getEnv() ];
    }
}
