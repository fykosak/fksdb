<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Semantics;

use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\Transitions\Holder\ParticipantHolder;
use FKSDB\Models\Transitions\Statement;

/**
 * @implements Statement<bool,BaseHolder|ParticipantHolder>
 */
class RegOpen implements Statement
{
    public function __invoke(...$args): bool
    {
        /** @var BaseHolder|ParticipantHolder $holder */
        [$holder] = $args;
        return $holder->getModel()->event->isRegistrationOpened();
    }
}
