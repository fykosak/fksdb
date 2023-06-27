<?php

declare(strict_types=1);

namespace FKSDB\Components\PDFGenerators\TeamSeating;

use FKSDB\Components\PDFGenerators\Providers\AbstractPageComponent;

abstract class SeatingPageComponent extends AbstractPageComponent
{
    public function getPageFormat(): string
    {
        return self::FORMAT_B5_LANDSCAPE;
    }
}
