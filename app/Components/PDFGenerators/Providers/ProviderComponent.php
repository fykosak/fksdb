<?php

declare(strict_types=1);

namespace FKSDB\Components\PDFGenerators\Providers;

use Fykosak\Utils\BaseComponent\BaseComponent;
use Nette\DI\Container;

final class ProviderComponent extends BaseComponent
{
    private AbstractPageComponent $pageComponent;
    private iterable $items;

    public function __construct(
        AbstractPageComponent $pageComponent,
        iterable $items,
        Container $container
    ) {
        parent::__construct($container);
        $this->pageComponent = $pageComponent;
        $this->items = $items;
    }

    protected function createComponentPage(): AbstractPageComponent
    {
        return $this->pageComponent;
    }

    private function innerRender(array $params = []): void
    {
        $this->getTemplate()->items = $this->items;
        $this->getTemplate()->params = $params;
        $this->getTemplate()->format = $this->pageComponent->getPageFormat();
    }

    public function renderPrint(array $params = []): void
    {
        $this->innerRender($params);
        $this->getTemplate()->render(__DIR__ . DIRECTORY_SEPARATOR . 'pages.print.latte');
    }

    public function renderPreview(array $params = []): void
    {
        $this->innerRender($params);
        $this->getTemplate()->render(__DIR__ . DIRECTORY_SEPARATOR . 'pages.preview.latte');
    }
}
