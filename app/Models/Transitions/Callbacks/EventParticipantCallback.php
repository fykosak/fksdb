<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Callbacks;

use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Holder\ParticipantHolder;

/**
 * @phpstan-extends MailCallback<ParticipantHolder>
 */
abstract class EventParticipantCallback extends MailCallback
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
