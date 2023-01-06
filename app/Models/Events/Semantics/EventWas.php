<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Semantics;

use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\Expressions\EvaluatedExpression;
use FKSDB\Models\Transitions\Holder\ModelHolder;

class EventWas extends EvaluatedExpression
{
    /**
     * @param $holder
     * @param ...$args
     */
    public function __invoke($holder, ...$args): bool
    {
        return $holder->event->begin->getTimestamp() <= time();
    }

    public function __toString(): string
    {
        return 'eventWas';
    }
}
