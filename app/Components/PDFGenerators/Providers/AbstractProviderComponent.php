<?php

declare(strict_types=1);

namespace FKSDB\Components\PDFGenerators\Providers;

use FKSDB\Components\Controls\BaseComponent;

abstract class AbstractProviderComponent extends BaseComponent
{
    public const FORMAT_A5_PORTRAIT = 'A5-portrait';
    // public const FORMAT_A5_LANDSCAPE = 'A5-landscape';

    // public const FORMAT_A4_PORTRAIT = 'A4-portrait';
    // public const FORMAT_A4_LANDSCAPE = 'A4-landscape';

    public const FORMAT_B5_LANDSCAPE = 'B5-landscape';

    // public const FORMAT_B4_LANDSCAPE = 'B4-landscape';

    final public function render(): void
    {
        $this->template->items = $this->getItems();
        switch ($this->getFormat()) {
            case self::FORMAT_A5_PORTRAIT:
                $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'provider-a5-portrait.latte');
                return;
            case self::FORMAT_B5_LANDSCAPE:
                $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'provider-b5-landscape.latte');
                return;
        }
        $this->template->render(__DIR__ . '/provider.latte');
    }

    abstract protected function getFormat(): string;

    abstract protected function createComponentPage(): AbstractPageComponent;

    abstract protected function getItems(): iterable;
}
