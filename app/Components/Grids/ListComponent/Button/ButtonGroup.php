<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\ListComponent\Button;

use FKSDB\Components\Grids\ListComponent\ItemComponent;

class ButtonGroup extends ItemComponent
{

    protected function getTemplatePath(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'group.latte';
    }
}
