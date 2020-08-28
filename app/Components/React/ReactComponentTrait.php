<?php

namespace FKSDB\Components\React;

use FKSDB\Application\IJavaScriptCollector;
use Nette\Application\BadRequestException;
use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;
use Nette\Utils\Json;
use Nette\Utils\JsonException;

/**
 * Trait ReactField
 * @author Michal Červeňák <miso@fykos.cz>
 */
trait ReactComponentTrait {

    private array $actions = [];

    private static bool $attachedJS = false;

    protected string $reactId;

    /**
     * @param string $reactId
     * @return void
     */
    protected function registerReact(string $reactId) {
        $this->reactId = $reactId;
        $this->registerMonitor();
    }

    /**
     * @param mixed ...$args
     * @throws BadRequestException
     * @throws JsonException Can be used only with BaseControl
     */
    protected function appendProperty(...$args) {
        if (!$this instanceof BaseControl) {
            throw new BadRequestException('method appendProperty can be used only with BaseControl');
        }
        $this->appendPropertyTo($this->control, ...$args);
    }

    /**
     * @param Html $html
     * @param mixed ...$args
     * @return void
     * @throws JsonException
     */
    protected function appendPropertyTo(Html $html, ...$args) {
        $this->configure();
        $html->setAttribute('data-react-root', true);
        $html->setAttribute('data-react-id', $this->reactId);
        $html->setAttribute('data-data', $this->getData(...$args));
        $html->setAttribute('data-actions', Json::encode($this->actions));
    }

    private function registerMonitor(): void {
        $this->monitor(IJavaScriptCollector::class, function (IJavaScriptCollector $collector) {
            if (!self::$attachedJS) {
                self::$attachedJS = true;
                $collector->registerJSFile('js/bundle.min.js');
            }
        });
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

    abstract public function getData(...$args): string;
}
