<?php

declare(strict_types=1);

declare(strict_types=1);

namespace FKSDB\Models\Authorization\EventRole;

use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Models\ModelEventOrg;

class EventOrgRole extends EventRole
{
    public ModelEventOrg $eventOrg;

    public function __construct(ModelEvent $event, ModelEventOrg $eventOrg)
    {
        parent::__construct('event.org', $event);
        $this->eventOrg = $eventOrg;
    }
}
