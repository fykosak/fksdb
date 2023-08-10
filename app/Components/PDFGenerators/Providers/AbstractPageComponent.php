<?php

declare(strict_types=1);

namespace FKSDB\Components\PDFGenerators\Providers;

use Fykosak\Utils\BaseComponent\BaseComponent;

/**
 * @template TRow
 * @template TParam of array
 */
abstract class AbstractPageComponent extends BaseComponent
{
    public const FORMAT_A5_PORTRAIT = 'a5-portrait';
    public const FORMAT_A5_LANDSCAPE = 'a5-landscape';

    public const FORMAT_A4_PORTRAIT = 'a4-portrait';
    public const FORMAT_A4_LANDSCAPE = 'a4-landscape';

    public const FORMAT_B5_LANDSCAPE = 'b5-landscape';
    public const FORMAT_B5_PORTRAIT = 'b5-portrait';

    /**
     * @phpstan-param TRow $row
     * @phpstan-param TParam $params
     */
    abstract public function render($row, array $params = []): void;

    abstract public function getPageFormat(): string;

    /**
     * @return array<string,string>
     */
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
