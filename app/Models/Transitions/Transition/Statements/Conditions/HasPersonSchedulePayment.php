<?php

namespace FKSDB\Models\Transitions\Transition\Statements\Conditions;

use FKSDB\Models\Transitions\Holder\PersonScheduleHolder;
use FKSDB\Models\Transitions\Statement;

/**
 * @phpstan-implements Statement<bool,PersonScheduleHolder>
 */
class HasPersonSchedulePayment implements Statement
{

    public function __invoke(...$args): bool
    {
        /** @var PersonScheduleHolder $holder */
        [$holder] = $args;
        return (bool)$holder->getModel()->getPayment();
    }
}
