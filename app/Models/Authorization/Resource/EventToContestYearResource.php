<?php

namespace FKSDB\Models\Authorization\Resource;

use FKSDB\Models\ORM\Models\ContestYearModel;

final class EventToContestYearResource implements ContestYearResource
{
    private EventResource $eventResource;

    public function __construct(EventResource $eventResource)
    {
        $this->eventResource = $eventResource;
    }

    public function getContestYear(): ContestYearModel
    {
        return $this->eventResource->getEvent()->getContestYear();
    }

    public function getResourceId(): string
    {
        return $this->eventResource->getResourceId();
    }
}
