<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Components\Container;

use FKSDB\Components\Grids\Components\ItemComponent;

class ListRows extends ItemComponent
{
    protected function getTemplatePath(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'listRows.latte';
    }
}