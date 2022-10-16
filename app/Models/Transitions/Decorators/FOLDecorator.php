<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Decorators;

use FKSDB\Models\Transitions\Machine\Machine;
use FKSDB\Models\Transitions\TransitionsDecorator;

class FOLDecorator implements TransitionsDecorator
{
    public function decorate(Machine $machine): void
    {
    }
}
