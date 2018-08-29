<?php

namespace  FKSDB\Components\Controls\Fyziklani;

use FKS\Application\IJavaScriptCollector;

use Nette\Utils\Json;

/**
 * Class Routing
 */
class RoutingEdit extends ReactComponent {
    /**
     * @var array
     */
    private $data;

    public function setData($data) {
        $this->data = $data;
    }

    /**
     * @throws \Nette\Utils\JsonException
     */
    public function render() {
        $this->template->data = Json::encode($this->data);
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'RoutingEdit.latte');
        $this->template->render();
    }
}
