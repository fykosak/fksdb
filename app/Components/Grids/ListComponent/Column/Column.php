<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\ListComponent\Column;

use FKSDB\Components\Grids\ListComponent\ItemComponent;

abstract class Column extends ItemComponent
{
    public string $className = 'col';
}
