<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\ListComponent;

use Fykosak\Utils\BaseComponent\BaseComponent;

abstract class Row extends BaseComponent
{
    public string $className = 'row';
}
