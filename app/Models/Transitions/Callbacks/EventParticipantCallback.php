<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Callbacks;

use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Holder\ParticipantHolder;

/**
 * @phpstan-extends MailCallback<BaseHolder|ParticipantHolder>
 */
abstract class EventParticipantCallback extends MailCallback
{
    /**
     * @param BaseHolder|ParticipantHolder $holder
     * @phpstan-return PersonModel[]
     */
    final protected function getPersonsFromHolder(ModelHolder $holder): array
    {
        return [$holder->getModel()->person];
    }
}
