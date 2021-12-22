<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\EventRole;

use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Models\ModelEventParticipant;

class ParticipantRole extends EventRole
{
    public ModelEventParticipant $eventParticipant;

    public function __construct(ModelEvent $event, ModelEventParticipant $eventParticipant)
    {
        parent::__construct('event.participant', $event);
        $this->eventParticipant = $eventParticipant;
    }
}
