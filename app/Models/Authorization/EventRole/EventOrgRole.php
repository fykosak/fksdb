<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\EventRole;

use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\EventOrgModel;

class EventOrgRole extends EventRole
{
    public EventOrgModel $eventOrganiser;

    public function __construct(EventModel $event, EventOrgModel $eventOrganiser)
    {
        parent::__construct('event.org', $event);
        $this->eventOrganiser = $eventOrganiser;
    }
}
