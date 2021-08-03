<?php

declare(strict_types=1);

namespace FKSDB\Components\PDFGenerators\Provider;

use FKSDB\Components\Controls\BaseComponent;
use FKSDB\Components\PDFGenerators\PageComponent;

abstract class AbstractProviderComponent extends BaseComponent
{
    public const FORMAT_A5 = 'A5';

    final public function render(): void
    {
        $this->template->items = $this->getItems();
        $this->template->render(__DIR__ . '/provider.latte');
    }

    abstract protected function getFormat(): string;

    abstract protected function createComponentPage(): PageComponent;

    abstract protected function getItems(): iterable;
}
