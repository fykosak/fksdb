<?php

namespace FKSDB\Components\React;

use FKSDB\Application\IJavaScriptCollector;

/**
 * Trait ReactField
 * @package FKSDB\Components\React
 */
trait ReactField {

    static private $attachedJS = false;

    protected function appendProperty() {
        $this->setAttribute('data-react-root', true);
        $this->setAttribute('data-module', $this->getModuleName());
        $this->setAttribute('data-component', $this->getComponentName());
        $this->setAttribute('data-mode', $this->getMode());
        $this->setAttribute('data-data', $this->getData());
    }
    protected function registerMonitor(){
        $this->monitor('FKSDB\Application\IJavaScriptCollector');
    }

    /**
     * @param $obj
     */
    protected function attachedReact($obj) {
        if (!self::$attachedJS && $obj instanceof IJavaScriptCollector) {
            self::$attachedJS = true;
            $obj->registerJSFile('js/bundle-all.min.js');
        }
    }
}
