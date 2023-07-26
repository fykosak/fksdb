<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Semantics;

use FKSDB\Models\Transitions\Statement;

/**
 * @implements Statement<bool>
 */
class RegOpen implements Statement
{
    public function __invoke(...$args): bool
    {
        [$holder] = $args;
        return (!$holder->event->registration_begin || $holder->event->registration_begin->getTimestamp() <= time())
            && (!$holder->event->registration_end || $holder->event->registration_end->getTimestamp() >= time());
    }
}
