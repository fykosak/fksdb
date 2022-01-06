<?php

declare(strict_types=1);

declare(strict_types=1);

namespace FKSDB\Components\PDFGenerators\Providers;

use FKSDB\Components\Controls\BaseComponent;

abstract class AbstractPageComponent extends BaseComponent
{
    public const FORMAT_A5_PORTRAIT = 'a5-portrait';
    public const FORMAT_A5_LANDSCAPE = 'a5-landscape';

    public const FORMAT_A4_PORTRAIT = 'a4-portrait';
    public const FORMAT_A4_LANDSCAPE = 'a4-landscape';

    public const FORMAT_B5_LANDSCAPE = 'b5-landscape';
    public const FORMAT_B5_PORTRAIT = 'b5-portrait';

    /**
     * @param mixed $row
     */
    abstract public function render($row): void;

    abstract public function getPagesTemplatePath(): string;

    final protected function formatPathByFormat(string $format): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'pages-' . $format . '.latte';
    }

    public static function getAvailableFormats(): array
    {
        return [
            self::FORMAT_A5_LANDSCAPE => _('A5 landscape'),
            self::FORMAT_A5_PORTRAIT => _('A5 portrait'),
            self::FORMAT_A4_LANDSCAPE => _('A4 landscape'),
            self::FORMAT_A4_PORTRAIT => _('A4 portrait'),
            self::FORMAT_B5_LANDSCAPE => _('B5 landscape'),
            self::FORMAT_B5_PORTRAIT => _('B5 portrait'),
        ];
    }
}
