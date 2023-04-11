<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Semantics;

use FKSDB\Models\Expressions\EvaluatedExpression;

class EventWas extends EvaluatedExpression
{
    public function __invoke(...$args): bool
    {
        [$holder] = $args;
        return $holder->event->begin->getTimestamp() <= time();
    }

    public function __toString(): string
    {
        return 'eventWas';
    }
}
