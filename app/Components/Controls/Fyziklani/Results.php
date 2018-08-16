<?php

namespace FKSDB\Components\Controls\Fyziklani;

use FKS\Application\IJavaScriptCollector;

/**
 * Class Results
 */
class Results extends ReactComponent {
    /**
     * @var bool
     */
    private static $JSAttached = false;

    /**
     * @throws \Nette\Utils\JsonException
     */
    public function render() {
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'Results.latte');
        $this->template->render();
    }

    protected function attached($obj) {
        parent::attached($obj);
        if (!static::$JSAttached && $obj instanceof IJavaScriptCollector) {
            static::$JSAttached = true;
            $obj->registerJSFile('js/tablesorter.min.js');
            $obj->registerJSFile('js/bundle-all.min.js');
        }
    }
}
