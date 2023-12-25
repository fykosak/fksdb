<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Transition\Statements\Conditions;

use FKSDB\Models\Transitions\Holder\PersonScheduleHolder;
use FKSDB\Models\Transitions\Statement;

/**
 * @phpstan-implements Statement<bool,PersonScheduleHolder>
 */
class IsPaid implements Statement
{
    /**
     * @throws \Exception
     */
    public function __invoke(...$args): bool
    {
        /** @var PersonScheduleHolder $holder */
        [$holder] = $args;
        $model = $holder->getModel();
        return $model->isPaid();
    }
}
