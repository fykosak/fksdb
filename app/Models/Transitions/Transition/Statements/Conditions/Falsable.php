<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Transition\Statements\Conditions;

use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Statement;

class Falsable implements Statement
{
    /**
     * @param mixed $holder
     */
    public function __invoke($holder, ...$args): bool
    {
        return false;
    }
}
