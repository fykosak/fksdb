<?php

declare(strict_types=1);

namespace FKSDB\Components\PDFGenerators\Providers;

use FKSDB\Components\Controls\BaseComponent;

abstract class AbstractPageComponent extends BaseComponent
{
    public const FORMAT_A5_PORTRAIT = 'a5-portrait';
    // public const FORMAT_A5_LANDSCAPE = 'A5-landscape';

    // public const FORMAT_A4_PORTRAIT = 'A4-portrait';
    // public const FORMAT_A4_LANDSCAPE = 'A4-landscape';

    public const FORMAT_B5_LANDSCAPE = 'b5-landscape';

    // public const FORMAT_B4_LANDSCAPE = 'B4-landscape';

    /**
     * @param mixed $row
     */
    abstract public function render($row): void;

    abstract public function getPagesTemplatePath(): string;

    final protected function formatPathByFormat(string $format): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'pages-' . $format . '.latte';
    }
}
