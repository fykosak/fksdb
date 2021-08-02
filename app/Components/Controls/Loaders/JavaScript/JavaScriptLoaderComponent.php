<?php

namespace FKSDB\Components\Controls\Loaders\JavaScript;

use FKSDB\Components\Controls\Loaders\WebLoaderComponent;

class JavaScriptLoaderComponent extends WebLoaderComponent
{
    protected function getDir(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR;
    }
}
