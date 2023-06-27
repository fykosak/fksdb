<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Semantics;

use FKSDB\Models\Transitions\Statement;

class EventWas implements Statement
{
    public function __invoke(...$args): bool
    {
        [$holder] = $args;
        return $holder->event->begin->getTimestamp() <= time();
    }
}
