<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Semantics;

use FKSDB\Models\Transitions\Holder\ParticipantHolder;
use FKSDB\Models\Transitions\Statement;

/**
 * @implements Statement<bool,ParticipantHolder>
 */
class EventWas implements Statement
{
    public function __invoke(...$args): bool
    {
        /** @var ParticipantHolder $holder */
        [$holder] = $args;
        return $holder->getModel()->event->begin->getTimestamp() <= time();
    }
}
