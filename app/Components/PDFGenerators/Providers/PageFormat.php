<?php

declare(strict_types=1);

namespace FKSDB\Components\PDFGenerators\Providers;

enum PageFormat: string
{
    case FORMAT_A5_PORTRAIT = 'a5-portrait';
    case FORMAT_A5_LANDSCAPE = 'a5-landscape';

    case FORMAT_A4_PORTRAIT = 'a4-portrait';
    case FORMAT_A4_LANDSCAPE = 'a4-landscape';

    case FORMAT_B5_LANDSCAPE = 'b5-landscape';
    case FORMAT_B5_PORTRAIT = 'b5-portrait';

    public function getName(): array
    {
        return match ($this) {
            self::FORMAT_A5_LANDSCAPE => _('A5 landscape'),
            self::FORMAT_A5_PORTRAIT => _('A5 portrait'),
            self::FORMAT_A4_LANDSCAPE => _('A4 landscape'),
            self::FORMAT_A4_PORTRAIT => _('A4 portrait'),
            self::FORMAT_B5_LANDSCAPE => _('B5 landscape'),
            self::FORMAT_B5_PORTRAIT => _('B5 portrait'),
        };
    }
}
