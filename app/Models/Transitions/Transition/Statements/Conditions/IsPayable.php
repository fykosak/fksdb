<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Transition\Statements\Conditions;

use FKSDB\Models\Transitions\Holder\PersonScheduleHolder;
use FKSDB\Models\Transitions\Statement;

/**
 * @phpstan-implements Statement<bool,PersonScheduleHolder>
 */
class IsPayable implements Statement
{
    public function __invoke(...$args): bool
    {
        /** @var PersonScheduleHolder $holder */
        [$holder] = $args;
        try {
            $holder->getModel()->checkPayment();
            return true;
        } catch (\Throwable$exception) {
            return false;
        }
    }
}
