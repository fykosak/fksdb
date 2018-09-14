<?php

namespace FKSDB\Components\React\Fyziklani;

use Nette\Utils\Json;

/**
 * Class Routing
 */
class RoutingEdit extends FyziklaniModule {
    /**
     * @var array
     */
    private $data;

    public function setData($data) {
        $this->data = $data;
    }

    public function getData() {
        return Json::encode($this->data);
    }

    protected function getMode() {
        return null;
    }

    protected function getComponentName() {
        return 'routing';
    }
}
