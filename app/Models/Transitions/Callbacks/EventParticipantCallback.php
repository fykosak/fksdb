<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Callbacks;

use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\Transitions\Holder\ModelHolder;

/**
 * @phpstan-extends MailCallback<BaseHolder>
 */
abstract class EventParticipantCallback extends MailCallback
{
    /**
     * @param BaseHolder $holder
     * @return PersonModel[]
     */
    final protected function getPersonsFromHolder(ModelHolder $holder): array
    {
        return [$holder->getModel()->person];
    }
}
