<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Semantics;

use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\Transitions\Statement;

/**
 * @implements Statement<bool,BaseHolder>
 */
class RegOpen implements Statement
{
    public function __invoke(...$args): bool
    {
        /** @var BaseHolder $holder */
        [$holder] = $args;
        return $holder->event->isRegistrationOpened();
    }
}
