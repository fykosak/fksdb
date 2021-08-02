<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Loaders\Stylesheet;

use FKSDB\Components\Controls\Loaders\WebLoaderComponent;

class StylesheetLoaderComponent extends WebLoaderComponent
{
    protected function getDir(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR;
    }
}
