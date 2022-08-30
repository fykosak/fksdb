<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Transition\Statements\Conditions;

use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Transition\Statements\Statement;

class Falsable implements Statement
{

    public function __invoke(ModelHolder $holder): bool
    {
        return false;
    }
}
