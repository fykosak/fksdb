<?php

declare(strict_types=1);

namespace FKSDB\Models\Email\Source;

use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Holder\ParticipantHolder;

/**
 * @phpstan-extends TransitionEmail<EventParticipantModel>
 */
abstract class EventParticipantCallback extends TransitionEmail
{
    /**
     * @param ParticipantHolder $holder
     * @phpstan-return PersonModel[]
     */
    final protected function getPersons(ModelHolder $holder): array
    {
        return [$holder->getModel()->person];
    }
}
