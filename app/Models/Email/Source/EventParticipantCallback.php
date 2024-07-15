<?php

declare(strict_types=1);

namespace FKSDB\Models\Email\Source;

use FKSDB\Models\Events\Model\Holder\BaseHolder as THolder;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\Transitions\Holder\ModelHolder;

//phpcs:disable
/**
 * @phpstan-template THolder of \FKSDB\Models\Events\Model\Holder\BaseHolder|\FKSDB\Models\Transitions\Holder\ParticipantHolder
 * @phpstan-extends TransitionEmail<THolder>
 */
// phpcs:enable
abstract class EventParticipantCallback extends TransitionEmail
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
