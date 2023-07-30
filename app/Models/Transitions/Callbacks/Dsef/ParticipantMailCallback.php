<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Callbacks\Dsef;

use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\Transitions\Callbacks\EventParticipantCallback;
use FKSDB\Models\Transitions\Holder\ModelHolder;

class ParticipantMailCallback extends EventParticipantCallback
{

    /**
     * @param BaseHolder $holder
     * @throws NotImplementedException
     */
    protected function getTemplatePath(ModelHolder $holder): string
    {
        throw new NotImplementedException();
    }

    /**
     * @param BaseHolder $holder
     * @throws NotImplementedException
     */
    protected function getData(ModelHolder $holder): array
    {
        throw new NotImplementedException();
    }
}
