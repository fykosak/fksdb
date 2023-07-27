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

    public function renderPrint(array $params = []): void
    {
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'pages.print.latte', [
            'items' => $this->items,
            'params' => $params,
            'format' => $this->pageComponent->getPageFormat(),
        ]);
    }

    public function renderPreview(array $params = []): void
    {
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'pages.preview.latte', [
            'items' => $this->items,
            'params' => $params,
            'format' => $this->pageComponent->getPageFormat(),
        ]);
    }
}
