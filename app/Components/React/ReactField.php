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
    private array $actions = [];

    private static bool $attachedJS = false;

    /**
     * @throws JsonException
     */
    protected function appendProperty(): void {
        $this->configure();
        $this->setAttribute('data-react-root', true);
        $this->setAttribute('data-react-id', $this->getReactId());
        $this->setAttribute('data-data', $this->getData());
        $this->setAttribute('data-actions', Json::encode($this->actions));
    }


    protected function attachedReact(IComponent $obj): void {
        if (!self::$attachedJS && $obj instanceof IJavaScriptCollector) {
            self::$attachedJS = true;
            $obj->registerJSFile('js/bundle.min.js');
        }
    }

    protected function registerMonitor(): void {
        $this->monitor(IJavaScriptCollector::class);
    }

    protected function configure(): void {
    }

    public function addAction(string $key, string $destination): void {
        $this->actions[$key] = $destination;
    }

    abstract protected function getReactId(): string;

    abstract public function getData(): string;
}
