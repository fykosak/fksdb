<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms\Fyziklani\StateStrategy;

use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamState;
use FKSDB\Models\Utils\FakeStringEnum;

class Strategy
{
    /**
     * @return FakeStringEnum&EnumColumn
     */
    public static function getNewState(EventModel $event, Logger $logger): ?TeamState
    {
        switch ($event->event_type_id) {
            case 1:
                return self::handleFOF($event, $logger);
            case 9:
                return self::handleFOL($event, $logger);
        }
        throw new \InvalidArgumentException();
    }

    private static function handleFOL(EventModel $event, Logger $logger): ?TeamState
    {
        if ($logger->isNew) {
            return TeamState::from(TeamState::Pending);
        }
        if ($logger->memberAdded) {
            return TeamState::from(TeamState::Pending);
        }
        return null;
    }

    private static function handleFOF(EventModel $event, Logger $logger): ?TeamState
    {
        if ($logger->isNew) {
            return TeamState::from(TeamState::Pending);
        }
        if ($logger->memberAdded) {
            return TeamState::from(TeamState::Pending);
        }
        return null;
    }
}
