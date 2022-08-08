<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\EventRole;

use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\EventParticipantModel;

class ParticipantRole extends EventRole
{
    public EventParticipantModel $eventParticipant;

    public function __construct(EventModel $event, EventParticipantModel $eventParticipant)
    {
        parent::__construct('event.participant', $event);
        $this->eventParticipant = $eventParticipant;
    }
}
