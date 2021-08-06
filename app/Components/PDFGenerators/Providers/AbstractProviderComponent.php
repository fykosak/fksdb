<?php

declare(strict_types=1);

namespace FKSDB\Components\PDFGenerators\Providers;

use FKSDB\Components\Controls\BaseComponent;

abstract class AbstractProviderComponent extends BaseComponent
{
    public const FORMAT_A5_PORTRAIT = 'a5-portrait';
    // public const FORMAT_A5_LANDSCAPE = 'A5-landscape';

    // public const FORMAT_A4_PORTRAIT = 'A4-portrait';
    // public const FORMAT_A4_LANDSCAPE = 'A4-landscape';

    public const FORMAT_B5_LANDSCAPE = 'b5-landscape';

    // public const FORMAT_B4_LANDSCAPE = 'B4-landscape';

    final public function render(): void
    {
        $this->template->items = $this->getItems();
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'provider-' . $this->getFormat() . '.latte');
    }

    abstract protected function getFormat(): string;

    abstract protected function createComponentPage(): AbstractPageComponent;

    abstract protected function getItems(): iterable;
}
