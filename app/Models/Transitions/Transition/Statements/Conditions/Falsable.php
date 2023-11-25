<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Transition\Statements\Conditions;

use FKSDB\Models\Transitions\Statement;

/**
 * @implements Statement<false,never>
 */
class Falsable implements Statement
{
    public function __invoke(...$args): bool
    {
        return false;
    }
}
