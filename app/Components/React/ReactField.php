<?php

namespace FKSDB\Components\React;

use FKSDB\Application\IJavaScriptCollector;
use Nette\Utils\Json;
use Nette\Utils\JsonException;

/**
 * Trait ReactField
 * @package FKSDB\Components\React
 */
trait ReactField {

    static private $attachedJS = false;

    /**
     * @throws JsonException
     */
    protected function appendProperty() {
        $this->setAttribute('data-react-root', true);
        $this->setAttribute('data-module', $this->getModuleName());
        $this->setAttribute('data-component', $this->getComponentName());
        $this->setAttribute('data-mode', $this->getMode());
        $this->setAttribute('data-data', $this->getData());
        $this->setAttribute('data-actions', Json::encode($this->getActions()));
    }

    protected function registerMonitor() {
        $this->monitor(IJavaScriptCollector::class);
    }

    /**
     * @param object $obj
     */
    protected function attachedReact($obj) {
        if (!self::$attachedJS && $obj instanceof IJavaScriptCollector) {
            self::$attachedJS = true;
            $obj->registerJSFile('js/bundle-all.min.js');
        }
    }

    /**
     * @return string
     */
    abstract function getModuleName(): string;

    /**
     * @return string
     */
    abstract function getComponentName(): string;

    /**
     * @return string
     */
    abstract function getMode(): string;

    /**
     * @return string
     */
    abstract function getData(): string;

    /**
     * @return string[]
     */
    abstract function getActions(): array;
}
