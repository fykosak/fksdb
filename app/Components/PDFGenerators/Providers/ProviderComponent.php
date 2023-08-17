<?php

declare(strict_types=1);

namespace FKSDB\Components\PDFGenerators\Providers;

use Fykosak\Utils\BaseComponent\BaseComponent;
use Nette\DI\Container;

/**
 * @phpstan-template TRow
 * @phpstan-template TParam of array
 */
final class ProviderComponent extends BaseComponent
{
    /** @phpstan-var AbstractPageComponent<TRow,TParam> */
    private AbstractPageComponent $pageComponent;
    /**
     * @phpstan-var iterable<TRow>
     */
    private iterable $items;

    /**
     * @phpstan-param AbstractPageComponent<TRow,TParam> $pageComponent
     * @phpstan-param iterable<TRow> $items
     */
    public function __construct(
        AbstractPageComponent $pageComponent,
        iterable $items,
        Container $container
    ) {
        parent::__construct($container);
        $this->pageComponent = $pageComponent;
        $this->items = $items;
    }

    /**
     * @phpstan-return AbstractPageComponent<TRow,TParam>
     */
    protected function createComponentPage(): AbstractPageComponent
    {
        return $this->pageComponent;
    }

    /**
     * @phpstan-param TParam $params
     */
    public function renderPrint(array $params = []): void
    {
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'pages.print.latte', [
            'items' => $this->items,
            'params' => $params,
            'format' => $this->pageComponent->getPageFormat(),
        ]);
    }

    /**
     * @phpstan-param TParam $params
     */
    public function renderPreview(array $params = []): void
    {
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'pages.preview.latte', [
            'items' => $this->items,
            'params' => $params,
            'format' => $this->pageComponent->getPageFormat(),
        ]);
    }
}
