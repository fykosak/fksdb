<?php

namespace FKSDB\Components\React;

use FKSDB\Application\IJavaScriptCollector;
use FKSDB\Logging\ILogger;
use FKSDB\Logging\MemoryLogger;
use FKSDB\Messages\Message;
use Nette\Application\BadRequestException;
use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;

/**
 * Trait ReactField
 * @author Michal Červeňák <miso@fykos.cz>
 */
trait ReactComponentTrait {

    /** @var bool */
    private static $attachedJS = false;

    /** @var MemoryLogger */
    private $logger;

    /** @var string */
    protected $reactId;

    /**
     * @param string $reactId
     * @return void
     */
    protected function registerReact(string $reactId) {
        $this->reactId = $reactId;
        $this->logger = new MemoryLogger();
        $this->registerMonitor();
    }

    /**
     * @throws BadRequestException
     */
    protected function appendProperty() {
        if (!$this instanceof BaseControl) {
            throw new BadRequestException('method appendProperty can be used only with BaseControl');
        }
        $this->appendPropertyTo($this->control);
    }

    /**
     * @param Html $html
     * @return void
     */
    protected function appendPropertyTo(Html $html) {
        $html->setAttribute('data-react-root', true);
        $html->setAttribute('data-react-id', $this->reactId);
        foreach ($this->getResponseData() as $key => $value) {
            $html->setAttribute('data-' . $key, $value);
        }
    }

    private function registerMonitor() {
        $this->monitor(IJavaScriptCollector::class, function (IJavaScriptCollector $collector) {
            if (!self::$attachedJS) {
                self::$attachedJS = true;
                $collector->registerJSFile('js/bundle.min.js');
            }
        });
    }

    protected function getLogger(): ILogger {
        return $this->logger;
    }

    /**
     * @return mixed|null
     */
    protected function getData() {
        return null;
    }

    /**
     * @return string[]
     */
    protected function getResponseData(): array {
        $data['messages'] = array_map(function (Message $value): array {
            return $value->__toArray();
        }, $this->logger->getMessages());
        $data['data'] = json_encode($this->getData());
        $this->logger->clear();
        return $data;
    }
}
