<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\ListComponent\Row;

use FKSDB\Components\Grids\ListComponent\ItemComponent;

abstract class Row extends ItemComponent
{
    public string $className = 'row mb-2';
}
