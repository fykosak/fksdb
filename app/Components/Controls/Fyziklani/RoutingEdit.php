<?php

namespace  FKSDB\Components\Controls\Fyziklani;

use FKS\Application\IJavaScriptCollector;

use Nette\Utils\Json;

/**
 * Class Routing
 * @package BrawlLib\Components
 */
class RoutingEdit extends ReactComponent {
    /**
     * @var array
     */
    private $data;

    /**
     * @var bool
     */
    private static $JSAttached = false;

    public function setData($data) {
        $this->data = $data;
    }

    public function render() {
        $this->template->data = Json::encode($this->data);

        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'RoutingEdit.latte');
        $this->template->render();
    }

    protected function attached($obj) {
        parent::attached($obj);
        if (!self::$JSAttached && $obj instanceof IJavaScriptCollector) {
            self::$JSAttached = true;
            $obj->registerJSFile('js/bundle-routing.min.js');
        }
    }
}
