<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Callbacks;

use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\Transitions\Holder\ModelHolder;

//phpcs:disable
/**
 * @phpstan-template THolder of \FKSDB\Models\Transitions\Holder\ParticipantHolder|\FKSDB\Models\Transitions\Holder\ParticipantHolder
 * @phpstan-extends MailCallback<THolder>
 */
// phpcs:enable
abstract class EventParticipantCallback extends MailCallback
{
    /**
     * @param THolder $holder
     * @phpstan-return PersonModel[]
     */
    final protected function getPersons(ModelHolder $holder): array
    {
        return [$holder->getModel()->person];
    }
}
