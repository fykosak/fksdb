<?php

declare(strict_types=1);

namespace FKSDB\Components\PDFGenerators\Providers;

use FKSDB\Components\Controls\BaseComponent;

abstract class AbstractPageComponent extends BaseComponent
{
    abstract public function render(mixed $row): void;

    abstract public function getPagesTemplatePath(): string;

    final protected function formatPathByFormat(PageFormat $format): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'pages-' . $format->value . '.latte';
    }
}
