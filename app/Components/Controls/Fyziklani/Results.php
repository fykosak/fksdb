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
     * @var string
     */
    private $mode;

    public function __construct($mode) {
        parent::__construct();
        $this->mode = $mode;
    }

    /**
     * @throws \Nette\Utils\JsonException
     */
    public function render() {
        $this->template->mode = $this->mode;
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
