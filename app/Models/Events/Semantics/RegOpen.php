<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Semantics;

use FKSDB\Models\Expressions\EvaluatedExpression;

class RegOpen extends EvaluatedExpression
{
    public function __invoke(...$args): bool
    {
        [$holder] = $args;
        return (!$holder->event->registration_begin || $holder->event->registration_begin->getTimestamp() <= time())
            && (!$holder->event->registration_end || $holder->event->registration_end->getTimestamp() >= time());
    }

    public function __toString(): string
    {
        return 'regOpen';
    }
}
