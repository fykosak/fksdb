<?php

declare(strict_types=1);

namespace FKSDB\Components\PDFGenerators\Providers;

use Nette\DI\Container;

final class DefaultProviderComponent extends AbstractProviderComponent
{
    private string $format;
    private AbstractPageComponent $pageComponent;
    private iterable $selection;

    public function __construct(
        AbstractPageComponent $pageComponent,
        string $format,
        iterable $selection,
        Container $container
    ) {
        parent::__construct($container);
        $this->format = $format;
        $this->pageComponent = $pageComponent;
        $this->selection = $selection;
    }

    final protected function getFormat(): string
    {
        return $this->format;
    }

    final protected function createComponentPage(): AbstractPageComponent
    {
        return $this->pageComponent;
    }

    final protected function getItems(): iterable
    {
        return $this->selection;
    }
}
