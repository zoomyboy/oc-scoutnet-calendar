<?php

namespace Zoomyboy\Scoutnet\Behaviors;

use \Backend\Behaviors\ListController;

class NestedListController extends ListController {
    use HasNested;

    public function index() {
        parent::index();

        $this->initNestedPage();
    }
}
