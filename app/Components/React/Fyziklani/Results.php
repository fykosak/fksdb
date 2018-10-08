<?php

namespace FKSDB\Components\React\Fyziklani;

use FKSDB\Application\IJavaScriptCollector;

class Results extends FyziklaniModule {
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

    public function getData(): string {
        return null;
    }


    protected function attached($obj) {
        parent::attached($obj);
        if (!static::$JSAttached && $obj instanceof IJavaScriptCollector) {
            static::$JSAttached = true;
            $obj->registerJSFile('js/tablesorter.min.js');
        }
    }

    public function getMode(): string {
        return $this->mode;
    }

    public function getComponentName(): string {
        return 'results';
    }
}
