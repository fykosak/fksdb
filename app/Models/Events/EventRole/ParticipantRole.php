<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\EventRole;

use FKSDB\Models\ORM\Models\ModelEventParticipant;

class ParticipantRole implements EventRole
{
    public ModelEventParticipant $eventParticipant;

    public function __construct(ModelEventParticipant $eventParticipant)
    {
        $this->eventParticipant = $eventParticipant;
    }
}
