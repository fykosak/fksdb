<?php

namespace FKSDB\Components\React;

use FKSDB\Application\IJavaScriptCollector;
use Nette\ComponentModel\IComponent;
use Nette\Utils\Json;
use Nette\Utils\JsonException;

/**
 * Trait ReactField
 * @author Michal Červeňák <miso@fykos.cz>
 */
trait ReactField {
    /**
     * @var string[]
     */
    private $actions = [];
    /**
     * @var bool
     */
    private static $attachedJS = false;

    /**
     * @throws JsonException
     */
    protected function appendProperty() {
        $this->configure();
        $this->setAttribute('data-react-root', true);
        $this->setAttribute('data-react-id', $this->getReactId());
        $this->setAttribute('data-data', $this->getData());
        $this->setAttribute('data-actions', Json::encode($this->actions));
    }

    /**
     * @param IComponent $obj
     */
    protected function attachedReact($obj) {
        if (!self::$attachedJS && $obj instanceof IJavaScriptCollector) {
            self::$attachedJS = true;
            $obj->registerJSFile('js/bundle.min.js');
        }
    }

    protected function registerMonitor() {
        $this->monitor(IJavaScriptCollector::class);
    }

    /**
     * @return void
     */
    protected function configure() {
    }

    /**
     * @param string $key
     * @param string $destination
     * @return void
     */
    public function addAction(string $key, string $destination) {
        $this->actions[$key] = $destination;
    }

    abstract protected function getReactId(): string;

    abstract public function getData(): string;
}
