<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Transition\Statements\Conditions;

use FKSDB\Models\Transitions\Transition\Statements\Statement;

class Falsable extends Statement
{
    protected function evaluate(...$args): bool
    {
        return false;
    }
}
