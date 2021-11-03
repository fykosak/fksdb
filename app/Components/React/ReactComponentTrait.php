<?php

declare(strict_types=1);

namespace FKSDB\Components\React;

use FKSDB\Components\Controls\Loaders\JavaScriptCollector;
use Fykosak\Utils\Logging\Logger;
use Fykosak\Utils\Logging\MemoryLogger;
use Fykosak\Utils\Logging\Message;
use Nette\Application\BadRequestException;
use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;

trait ReactComponentTrait
{

    private MemoryLogger $logger;

    private static bool $attachedJS = false;

    protected string $reactId;

    protected function registerReact(string $reactId): void
    {
        $this->reactId = $reactId;
        $this->logger = new MemoryLogger();
        $this->registerMonitor();
    }

    /**
     * @throws BadRequestException
     * @note Can be used only with BaseControl
     */
    protected function appendProperty(): void
    {
        if (!$this instanceof BaseControl) {
            throw new BadRequestException('method appendProperty can be used only with BaseControl');
        }
        $this->appendPropertyTo($this->control);
    }

    protected function appendPropertyTo(Html $html): void
    {
        $html->setAttribute('data-react-root', true);
        $html->setAttribute('data-react-id', $this->reactId);
        foreach ($this->getResponseData() as $key => $value) {
            $html->setAttribute('data-' . $key, $value);
        }
    }

    private function registerMonitor(): void
    {
        $this->monitor(JavaScriptCollector::class, function (JavaScriptCollector $collector) {
            if (!self::$attachedJS) {
                self::$attachedJS = true;
                $collector->registerJSFile('js/bundle.min.js');
            }
        });
    }

    protected function getLogger(): Logger
    {
        return $this->logger;
    }

    /**
     * @return mixed|null
     */
    protected function getData()
    {
        return null;
    }

    /**
     * @return string[]
     */
    protected function getResponseData(): array
    {
        $data['messages'] = array_map(fn(Message $value): array => $value->__toArray(), $this->logger->getMessages());
        $data['data'] = json_encode($this->getData());
        $this->logger->clear();
        return $data;
    }
}
