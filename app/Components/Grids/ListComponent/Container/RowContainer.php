<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\ListComponent\Container;

use FKSDB\Components\Grids\ListComponent\ItemComponent;

class RowContainer extends ItemComponent
{
    protected function getTemplatePath(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'row.latte';
    }
}
